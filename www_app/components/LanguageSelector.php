<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\Cookie;
use yii\console\Application as ConsoleApplication;

class LanguageSelector extends Component
{
    public string $paramName = 'lang';
    public string $userAttribute = 'language_code';
    public string $default = 'en';

    // Language table config
    public string $tableName = 'language';
    public string $codeField = 'code';
    public string $enabledField = 'is_enabled';
    public string $orderField = 'order';
    public ?\yii\db\Connection $db = null;

    public function detect(bool $isApi = false): string
    {
        if (Yii::$app instanceof ConsoleApplication) {
            return $this->default;
        }

        $param = $this->paramName;
        $lang = null;

        // 1. POST
        $lang = $this->extractValidLang(Yii::$app->request->post($param));
        if (!is_null($lang)) {
            return $this->finalize($lang, $isApi);
        }

        // 2. GET
        $lang = $this->extractValidLang(Yii::$app->request->get($param));
        if (!is_null($lang)) {
            return $this->finalize($lang, $isApi);
        }

        // 3. User profile
        if (!Yii::$app->user->isGuest) {
            $lang = $this->extractValidLang(Yii::$app->user->identity->{$this->userAttribute} ?? null);
            if (!is_null($lang)) {
                return $this->finalize($lang, $isApi);
            }
        }

        // 4. Session (only for web)
        if (!$isApi && Yii::$app->has('session')) {
            $lang = $this->extractValidLang(Yii::$app->session->get($param));
            if (!is_null($lang)) {
                return $this->finalize($lang, $isApi);
            }
        }

        // 5. Cookies (only for web)
        if (!$isApi && Yii::$app->request->cookies->has($param)) {
            $lang = $this->extractValidLang(Yii::$app->request->cookies->getValue($param));
            if (!is_null($lang)) {
                return $this->finalize($lang, $isApi);
            }
        }

        // 6. Accept-Language HTTP header
        if (Yii::$app->request->headers->has('Accept-Language')) {
            $lang = $this->extractValidLang(Yii::$app->request->headers->get('Accept-Language'));
            if (!is_null($lang)) {
                return $this->finalize($lang, $isApi);
            }
        }

        // 7. Fallback to default
        return $lang ?? $this->default;
    }

    protected function finalize(string $lang, bool $isApi): ?string
    {
        // Save to session/cookies/user only if the value is NOT null
        // session and cookies
        if (!$isApi) {
            $param = $this->paramName;
            Yii::$app->session->set($param, $lang);
            Yii::$app->response->cookies->add(new Cookie([
                'name' => $param,
                'value' => $lang,
                'expire' => time() + 3600 * 24 * 365,
            ]));
        }

        // save to user profile if authorized
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            if ($user->{$this->userAttribute} !== $lang) {
                $user->{$this->userAttribute} = $lang;
                $user->save(false, [$this->userAttribute]);
            }
        }

        return $lang;
    }

    /**
     * Extract and validate one or more language values
     */
    protected function extractValidLang($input): ?string
    {
        $prioritized = [];

        if (is_array($input)) {
            foreach ($input as $lang) {
                $prioritized[$lang] = 1.0;
            }
        } elseif (is_string($input)) {
            // Split into entries like "en-US;q=0.8"
            $entries = array_map('trim', explode(',', $input));

            foreach ($entries as $entry) {
                $parts = explode(';', $entry);
                $lang = trim($parts[0]);
                if ($lang !== '') {
                    $isQ = isset($parts[1]) && preg_match('/q=([0-9.]+)/', $parts[1], $matches);
                    $prioritized[$lang] = $isQ ? floatval($matches[1]) : 1.0;
                }
            }
        }

        // Sort by priority descending
        arsort($prioritized, SORT_NUMERIC);

        // Normalize to two-letter codes, preserve priority, remove duplicates
        $normalized = [];
        foreach (array_keys($prioritized) as $langCode) {
            $shortCode = strtolower(substr($langCode, 0, 2));
            if (!isset($normalized[$shortCode])) {
                $normalized[$shortCode] = $prioritized[$langCode];
            }
        }

        // Match against allowed languages
        $valid = $this->getAllowedLanguages();
        foreach (array_keys($normalized) as $lang) {
            if (in_array($lang, $valid, true)) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Get list of allowed languages from DB
     */
    protected function getAllowedLanguages(): array
    {
        $db = $this->db ?? Yii::$app->db;

        return Yii::$app->cache->getOrSet("allowed_languages_{$this->tableName}", function () use ($db) {
            return (new \yii\db\Query())
                ->select([$this->codeField])
                ->from($this->tableName)
                ->where([$this->enabledField => 1])
                ->orderBy([$this->orderField => SORT_ASC])
                ->column($db);
        }, 3600);
    }

}
