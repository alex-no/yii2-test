<?php
namespace app\migrations\base;

use yii\db\Migration;

/**
 * BaseCsvMigration
 *
 * Small base class to be extended by concrete migrations that import CSV files.
 * Keep this class in: www_app/migrations/base/
 */
abstract class BaseCsvMigration extends Migration
{
    use CsvSeedTrait;

    /** @var string Relative or absolute CSV filename (e.g. 'phone_code.csv') */
    protected $csvFile;

    /** @var string DB table name (e.g. '{{%phone_code}}') */
    protected $tableName;

    /** @var bool CSV contains header row */
    protected $hasHeader = true;

    /**
     * @var array|null If CSV has no header provide DB columns in CSV order,
     * e.g. ['code', 'operator_name']
     */
    protected $csvColumns = null;

    /**
     * @var array|null Optional mapping from CSV header name -> DB column name
     * e.g. ['code' => 'code', 'operator_name' => 'operator_name']
     */
    protected $csvHeaderToDb = null;

    /** @var int batch insert size */
    protected $batchSize = 500;

    // other properties (delimiter, enclosure etc.) inherit defaults from trait
}
