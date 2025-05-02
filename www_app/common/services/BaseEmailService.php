<?php

namespace app\common\services;

use Symfony\Component\Mime\Email;
use Yii;

abstract class BaseEmailService
{
    protected function renderTemplate(string $templatePath, array $params): array
    {
        /** @var array{html: string, text: string} $content */
        $content = Yii::$app->view->renderPhpFile(Yii::getAlias($templatePath), $params);

        if (!isset($content['html'], $content['text'])) {
            throw new \RuntimeException("Email template must return an array with 'html' and 'text' keys.");
        }

        return $content;
    }

    protected function sendEmail(string $to, string $subject, array $content, string $from = 'noreply@example.com'): bool
    {
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($content['html'])
            ->text($content['text']);

        return Yii::$app->mailer->send($email);
    }
}
