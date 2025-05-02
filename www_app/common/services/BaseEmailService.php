<?php

namespace app\common\services;

use Yii;
use app\components\SymfonyMailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use yii\base\InvalidArgumentException;

abstract class BaseEmailService
{
    protected function renderTemplate(string $templatePath, array $params): array
    {
        $file = Yii::getAlias($templatePath);

        if (!file_exists($file)) {
            throw new InvalidArgumentException("Email template not found: {$file}");
        }

        extract($params, EXTR_OVERWRITE);
        $content = require $file;

        if (!is_array($content) || !isset($content['html'], $content['text'])) {
            throw new InvalidArgumentException("Email template must return array with 'html' and 'text' keys.");
        }

        return $content;
    }

    protected function sendEmail(string $to, string $subject, array $content, ?string $from = null, array $cc = [], array $bcc = []): bool
    {
        try {
            /** @var \app\components\SymfonyMailer $email */
            $email = Yii::$app->mailer;
            return $email->send($to, $subject, $content, $from, $cc, $bcc);
        } catch (TransportExceptionInterface|\Throwable $e) {
            Yii::error("Failed to send email to {$to}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
