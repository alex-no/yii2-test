<?php
namespace app\migrations\base;

use yii\base\InvalidConfigException;

/**
 * CsvSeedTrait
 *
 * Trait to be used inside Yii2 migration classes for loading CSV data files.
 * The migration (base) class MUST provide configuration properties listed below.
 *
 * Design goals:
 * - Keep CSV parsing logic reusable and testable
 * - Support header-based mapping or explicit column mapping
 * - Use batchInsert() for efficient inserts
 * - Handle common CSV issues: BOM, encoding, delimiters, empty rows
 *
 * Usage:
 * - Create a base migration class that sets protected properties (see BaseCsvMigration example below)
 * - Use $this->importCsv() from your migration's up()/safeUp()
 * - Use $this->removeSeedData() from down()/safeDown() if desired
 *
 * Note: This trait doesn't assume ownership of transactions — call it from safeUp()/safeDown()
 * when you want transactional behaviour. If your DB/DDL operations are not transactional,
 * use up()/down() instead and be careful.
 */
trait CsvSeedTrait
{
    /**
     * Import CSV data into DB table using batchInsert.
     * The migration class using the trait must define the configuration properties
     * described below (csvFile, tableName, csvColumns or hasHeader, etc.).
     *
     * @return int number of rows inserted
     * @throws \RuntimeException on IO or parsing errors
     */
    public function importCsv(): int
    {
        $cfg = $this->getCsvConfig();

        $path = $this->resolveCsvPath($cfg['csvFile']);
        if (!is_readable($path)) {
            throw new \RuntimeException("CSV file not readable: {$path}");
        }

        $fh = fopen($path, 'rb');
        if ($fh === false) {
            throw new \RuntimeException("Unable to open CSV file: {$path}");
        }

        // Try to apply on-the-fly conversion from source encoding -> UTF-8 using stream filter.
        // If not possible, we'll mark $needConvertForRow = true and do per-cell mb_convert_encoding().
        $needConvertForRow = false;
        $sourceEncoding = $cfg['csvEncoding'] ?? null;
        if ($sourceEncoding !== null) {
            // Normalize common names (allow 'CP1251' or 'WINDOWS-1251')
            $sourceEncoding = strtoupper($sourceEncoding);
            // If encoding is already UTF-8, no conversion required
            if ($sourceEncoding !== 'UTF-8' && $sourceEncoding !== 'UTF8') {
                $filterName = "convert.iconv.{$sourceEncoding}/UTF-8";
                // Try to attach stream filter (preferred, efficient)
                set_error_handler(function ($errno, $errstr) { /* silence filter warnings */ });
                $filter = @stream_filter_append($fh, $filterName, STREAM_FILTER_READ);
                restore_error_handler();
                if ($filter === false) {
                    // stream filter unavailable — fallback to mb_convert_encoding per-field
                    $needConvertForRow = true;
                    if (!function_exists('mb_convert_encoding')) {
                        fclose($fh);
                        throw new \RuntimeException("CSV encoding conversion requested ({$sourceEncoding}) but neither iconv stream filter nor mbstring (mb_convert_encoding) are available.");
                    }
                }
            }
        }

        // Optionally skip BOM
        if ($cfg['skipBom']) {
            $this->skipBom($fh);
        }

        $rowCount = 0;
        $inserted = 0;
        $buffer = [];

        // determine columns (header or explicit mapping)
        // If header-driven mapping — read header first
        $columns = null;
        if ($cfg['hasHeader']) {
            $header = $this->fgetcsv_safe($fh, $cfg['length'], $cfg['delimiter'], $cfg['enclosure']);
            if ($header === false) {
                fclose($fh);
                throw new \RuntimeException('CSV header expected but file seems empty or invalid.');
            }
            // If stream filter wasn't available and we must convert, convert header fields
            if (!empty($needConvertForRow) && !empty($sourceEncoding)) {
                foreach ($header as $i => $val) {
                    $header[$i] = $val === null ? null : mb_convert_encoding($val, 'UTF-8', $sourceEncoding);
                }
            }
            $columns = $this->normalizeHeader($header);
        } elseif (!empty($cfg['csvColumns'])) {
            // explicit mapping provided by migration
            $columns = array_values($cfg['csvColumns']);
        } else {
            fclose($fh);
            throw new InvalidConfigException('CSV mapping not provided: set hasHeader = true or csvColumns');
        }

        // Validate columns vs DB table: optional - user may skip this check
        if ($cfg['validateColumns']) {
            // attempt to get table schema and compare column names
            try {
                $schema = \Yii::$app->db->schema->getTableSchema($cfg['tableName']);
                if ($schema === null) {
                    fclose($fh);
                    throw new InvalidConfigException("Table not found: {$cfg['tableName']}");
                }
                // check each column exists
                foreach ($columns as $colName) {
                    if (!isset($schema->columns[$colName])) {
                        fclose($fh);
                        throw new InvalidConfigException("Column '{$colName}' not found in table {$cfg['tableName']}");
                    }
                }
            } catch (\Throwable $e) {
                fclose($fh);
                throw $e;
            }
        }

        // Read rows
        while (($data = $this->fgetcsv_safe($fh, $cfg['length'], $cfg['delimiter'], $cfg['enclosure'])) !== false) {
            $rowCount++;
            // Skip empty rows if configured
            if ($cfg['skipEmpty'] && $this->isRowEmpty($data)) {
                continue;
            }

            // If explicit mapping uses associative csvColumns mapping: ['db_col' => 0 or 'csv_name']
            if (!empty($cfg['csvColumns']) && !$cfg['hasHeader']) {
                // csvColumns may be list of db column names in order OR associative mapping
                if ($this->isAssoc($cfg['csvColumns'])) {
                    // mapping: 'db_col' => csv_index_or_name
                    $row = [];
                    foreach ($cfg['csvColumns'] as $dbCol => $csvKey) {
                        if (is_int($csvKey)) {
                            $row[$dbCol] = $data[$csvKey] ?? null;
                        } else {
                            // if named, attempt to find header index (not available here) -> null
                            $row[$dbCol] = null;
                        }
                    }
                    $buffer[] = $row;
                } else {
                    // simple indexed list of db columns in CSV order
                    $mapped = [];
                    foreach ($cfg['csvColumns'] as $i => $dbCol) {
                        $mapped[$dbCol] = $data[$i] ?? null;
                    }
                    $buffer[] = $mapped;
                }
            } else {
                // header-driven: $columns contains CSV header names that should map to DB column names
                // We expect the migration to provide csvHeaderToDb mapping optionally
                if (!empty($cfg['csvHeaderToDb']) && is_array($cfg['csvHeaderToDb'])) {
                    $assoc = [];
                    foreach ($columns as $i => $csvName) {
                        $dbCol = $cfg['csvHeaderToDb'][$csvName] ?? null;
                        if ($dbCol !== null) {
                            $assoc[$dbCol] = $data[$i] ?? null;
                        }
                    }
                    $buffer[] = $assoc;
                } else {
                    // Default: treat header names as DB columns directly
                    $assoc = [];
                    foreach ($columns as $i => $csvName) {
                        $assoc[$csvName] = $data[$i] ?? null;
                    }
                    $buffer[] = $assoc;
                }
            }

            // Flush buffer when reaches batch size
            if (count($buffer) >= $cfg['batchSize']) {
                $this->flushBuffer($cfg['tableName'], $buffer);
                $inserted += count($buffer);
                $buffer = [];
            }
        }
        // If stream filter unavailable, convert each field from source encoding -> UTF-8
        if (!empty($needConvertForRow) && !empty($sourceEncoding) && is_array($data)) {
            foreach ($data as $i => $val) {
                $data[$i] = $val === null ? null : mb_convert_encoding($val, 'UTF-8', $sourceEncoding);
            }
        }

        // final flush
        if (!empty($buffer)) {
            $this->flushBuffer($cfg['tableName'], $buffer);
            $inserted += count($buffer);
        }

        fclose($fh);

        if ($cfg['verbose']) {
            echo "Imported {$inserted} rows into {$cfg['tableName']} (read {$rowCount} CSV rows)\n";
        }

        return $inserted;
    }

    /**
     * Remove seed data. Default implementation deletes rows by provided condition if set,
     * otherwise truncates the table. Customize in migration if needed.
     *
     * @return int number of affected rows or -1 for truncate
     */
    public function removeSeedData(): int
    {
        $cfg = $this->getCsvConfig();
        if (!empty($cfg['deleteCondition'])) {
            return \Yii::$app->db->createCommand()->delete($cfg['tableName'], $cfg['deleteCondition'])->execute();
        }

        // truncate if allowed
        if ($cfg['allowTruncate']) {
            $this->truncateTable($cfg['tableName']);
            return -1;
        }

        // otherwise nothing
        return 0;
    }

    // -------------------- helpers --------------------

    /**
     * Get configuration from migration class that uses this trait.
     * Expected properties (example names):
     * - protected string $csvFile; // relative path or absolute
     * - protected string $tableName;
     * - protected bool $hasHeader = true;
     * - protected array|null $csvColumns = null; // explicit column list or mapping
     * - protected array|null $csvHeaderToDb = null; // map csv header -> db column
     * - protected int $batchSize = 1000;
     * - protected string $delimiter = ',';
     * - protected string $enclosure = '"';
     * - protected int $length = 0;
     * - protected bool $skipEmpty = true;
     * - protected bool $skipBom = true;
     * - protected bool $validateColumns = true;
     * - protected bool $verbose = false;
     * - protected bool $allowTruncate = false;
     * - protected array|string|null $deleteCondition = null;
     *
     * @return array
     * @throws InvalidConfigException
     */
    protected function getCsvConfig(): array
    {
        // properties are read from the migration instance ($this)
        $defaults = [
            'csvFile' => null,
            'tableName' => null,
            'hasHeader' => true,
            'csvColumns' => null,
            'csvHeaderToDb' => null,
            'batchSize' => 500,
            'delimiter' => ';',
            'enclosure' => '"',
            'length' => 0,
            'skipEmpty' => true,
            'skipBom' => true,
            'validateColumns' => true,
            'verbose' => true,
            'allowTruncate' => false,
            'deleteCondition' => null,
            // source CSV encoding, e.g. 'CP1251' or 'WINDOWS-1251'. Null = assume UTF-8 / no conversion.
            'csvEncoding' => null,
        ];

        $cfg = [];
        foreach ($defaults as $k => $v) {
            $cfg[$k] = property_exists($this, $k) ? $this->$k : $v;
        }

        if (empty($cfg['csvFile']) || empty($cfg['tableName'])) {
            throw new InvalidConfigException('Migration must set $csvFile and $tableName properties when using CsvSeedTrait');
        }

        return $cfg;
    }

    /**
     * Resolve CSV path according to project layout.
     * Trait is inside: www_app/migrations/base
     * CSV folder is:    www_app/migrations/csv
     *
     * Accepts absolute path or relative filename.
     *
     * @param string $csvFile
     * @return string
     */
    protected function resolveCsvPath(string $csvFile): string
    {
        // Common locations: migration directory, migrations/sql, migrations/csv
        // absolute path -> return as-is
        if (strpos($csvFile, DIRECTORY_SEPARATOR) === 0 || preg_match('~^[A-Za-z]:\\\\~', $csvFile)) {
            return $csvFile; // absolute
        }

        // parent of this trait folder: www_app/migrations
        $migrationsDir = dirname(__DIR__);
        $candidate = $migrationsDir . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR . $csvFile;
        if (file_exists($candidate)) {
            return $candidate;
        }

        // fallback: same dir as trait (not usual, but safe)
        $candidate = __DIR__ . DIRECTORY_SEPARATOR . $csvFile;
        if (file_exists($candidate)) {
            return $candidate;
        }

        // fallback: relative to current working dir
        // last resort: relative to cwd
        return getcwd() . DIRECTORY_SEPARATOR . $csvFile;
    }

    protected function skipBom($fh): void
    {
        $firstBytes = fread($fh, 3);
        // UTF-8 BOM
        if ($firstBytes !== "\xEF\xBB\xBF") {
            // rewind if not BOM
            rewind($fh);
        }
    }

    protected function fgetcsv_safe($handle, $length = 0, $delimiter = ',', $enclosure = '"')
    {
        // Use native fgetcsv which handles escaping; return false on EOF
        return fgetcsv($handle, $length, $delimiter, $enclosure);
    }

    protected function normalizeHeader(array $header): array
    {
        return array_map(function ($h) {
            // trim and remove invisible chars
            return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $h));
        }, $header);
    }

    protected function isRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && $cell !== '') {
                return false;
            }
        }
        return true;
    }

    protected function isAssoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Default flush implementation — performs batchInsert.
     * Can be overridden in concrete migration to add timestamps, etc.
     *
     * @param string $tableName
     * @param array $buffer
     */
    protected function flushBuffer(string $tableName, array $buffer): void
    {
        if (empty($buffer)) return;

        // All rows in buffer must have identical keys (columns).
        $columns = array_keys(reset($buffer));
        $rows = [];
        foreach ($buffer as $r) {
            $rows[] = array_map(function ($v) {
                // convert empty strings to nulls if needed or keep as-is
                return $v === '' ? null : $v;
            }, array_values($r));
        }

        \Yii::$app->db->createCommand()->batchInsert($tableName, $columns, $rows)->execute();
    }
}
