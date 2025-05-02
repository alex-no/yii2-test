<?php

namespace app\components;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SymfonyMailerBase;
use Symfony\Component\Mime\Email;
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

        $resolvedDsn = str_replace('@runtime', Yii::getAlias('@runtime'), $this->dsn);
        $transport = Transport::fromDsn($resolvedDsn);
        $this->mailer = new SymfonyMailerBase($transport);
    }

    public function send(string $to, string $subject, string $template, array $params = []): bool
    {
        $view = Yii::$app->getView();
        // Render HTML and plain text versions of the email
        $htmlBody = $view->render($template, $params);
        $textBody = strip_tags($htmlBody); // You can create a more refined plain-text version

        $email = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject($subject)
            ->html($htmlBody)
            ->text($textBody);

        try {
            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            Yii::error("Email send error: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
