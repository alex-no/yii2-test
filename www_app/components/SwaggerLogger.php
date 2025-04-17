<?php

namespace app\components;

use Psr\Log\LoggerInterface;
use Yii;
use yii\log\Logger;

class SwaggerLogger implements LoggerInterface
{
    public const CATEGORY = 'swagger';

    public function emergency($message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $yiiLevel = $this->convertLevel($level);
        $interpolated = $this->interpolate($message, $context);
        Yii::getLogger()->log($interpolated, $yiiLevel, self::CATEGORY);
    }

    protected function convertLevel(string $level): int
    {
        return match (strtolower($level)) {
            'debug' => Logger::LEVEL_TRACE,
            'info', 'notice' => Logger::LEVEL_INFO,
            'warning' => Logger::LEVEL_WARNING,
            'error', 'critical', 'alert', 'emergency' => Logger::LEVEL_ERROR,
            default => Logger::LEVEL_INFO,
        };
    }

    protected function interpolate(string $message, array $context): string
    {
        foreach ($context as $key => $val) {
            $message = str_replace("{{$key}}", (string) $val, $message);
        }
        return $message;
    }
}
