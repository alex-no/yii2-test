<?php

namespace app\common\services;

use Symfony\Component\Mime\Email;
use Yii;

class EmailService extends BaseEmailService
{
    public function sendConfirmation(string $userEmail, string $userName, string $confirmUrl): bool
    {
        $params  = [
            'userName' => $userName,
            'userEmail' => $userEmail,
            'confirmUrl' => $confirmUrl,
        ];

        $content = $this->renderTemplate('@app/common/mail/confirm_email.php', $params);
        return $this->sendEmail($userEmail, 'Please confirm your email', $content, null, ['support@example.com']);
    }
}
