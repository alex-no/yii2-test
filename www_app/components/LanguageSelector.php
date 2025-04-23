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
        $request = Yii::$app->request;

        foreach ([
            fn() => $this->extractValidLang($request->post($param)), // 1. POST
            fn() => $this->extractValidLang($request->get($param)), // 2. GET
            fn() => !$this->checkGuest() ? $this->extractValidLang(Yii::$app->user->identity->{$this->userAttribute} ?? null) : null, // 3. User profile
            fn() => !$isApi && Yii::$app->has('session') ? $this->extractValidLang(Yii::$app->session->get($param)) : null, // 4. Session (only for web)
            fn() => !$isApi && $request->cookies->has($param) ? $this->extractValidLang($request->cookies->getValue($param)) : null, // 5. Cookies (only for web)
            fn() => $request->headers->has('Accept-Language') ? $this->extractValidLang($request->headers->get('Accept-Language')) : null, // 6. Accept-Language header
        ] as $resolver) {
            $lang = $resolver();
            if ($lang) {
                return $this->finalize($lang, $isApi);
            }
        }

        return $this->default; // 7. Default language
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
        if (!$this->checkGuest()) {
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

        if (empty($input)) {
            return null;
        } elseif (is_array($input)) {
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

    private function checkGuest(): bool
    {
        $user = Yii::$app->user;
        if ($user->isGuest && Yii::$app->request->headers->has('Authorization')) {
            $auth = new JwtAuth();
            $identity = $auth->authenticate(Yii::$app->user, Yii::$app->request);

            if ($identity) {
                $user->login($identity);
            }
        }
        return $user->isGuest;
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
