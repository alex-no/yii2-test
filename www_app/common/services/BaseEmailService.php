<?php

namespace app\common\services;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Yii;
use yii\base\InvalidArgumentException;

abstract class BaseEmailService
{
    protected function renderTemplate(string $templatePath, array $params): array
    {
        $content = Yii::$app->view->renderPhpFile(Yii::getAlias($templatePath), $params);

        if (!is_array($content) || !isset($content['html'], $content['text'])) {
            throw new InvalidArgumentException("Email template must return array with 'html' and 'text' keys.");
        }

        return $content;
    }

    protected function sendEmail(string $to, string $subject, array $content, ?string $from = null, array $cc = [], array $bcc = []): bool
    {
        try {
            $email = (new Email())
                ->from($from ?? Yii::$app->params['adminEmail'] ?? 'noreply@example.com')
                ->to($to)
                ->subject($subject)
                ->html($content['html'])
                ->text($content['text']);

            if (!empty($cc)) {
                $email->cc(...$cc);
            }

            if (!empty($bcc)) {
                $email->bcc(...$bcc);
            }

            return Yii::$app->mailer->send($email);
        } catch (TransportExceptionInterface|\Throwable $e) {
            Yii::error("Failed to send email to {$to}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
