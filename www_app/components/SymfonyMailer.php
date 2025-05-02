<?php

namespace app\components;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SymfonyMailerBase;
use Symfony\Component\Mime\Email;
use app\components\mailer\FileTransport;
use yii\base\Component;
use Yii;

class SymfonyMailer extends Component
{
    public string $dsn; // mailer DSN, e.g., smtp:// or stream://
    public string $from = 'no-reply@example.com';

    private SymfonyMailerBase $mailer;

    public function init(): void
    {
        parent::init();

        if (str_starts_with($this->dsn, 'stream://')) {
            $path = substr($this->dsn, strlen('stream://'));
            $transport = new FileTransport($path);
        } else {
            $transport = Transport::fromDsn($this->dsn);
        }

        $this->mailer = new SymfonyMailerBase($transport);
    }

    public function send(string $to, string $subject, array $content, ?string $from = null, array $cc = [], array $bcc = []): ?bool
    {
        $email = (new Email())
            ->from($from ?? $this->from)
            ->to($to)
            ->subject($subject)
            ->html($content['html'] ?? '')
            ->text($content['text'] ?? '');

        if (!empty($cc)) {
            $email->cc(...$cc);
        }

        if (!empty($bcc)) {
            $email->bcc(...$bcc);
        }

        try {
            return $this->mailer->send($email) === null;
        } catch (\Throwable $e) {
            Yii::error("Email send error: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
