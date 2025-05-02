<?php
/**
 * @var string $userName
 * @var string $userEmail
 * @var string $confirmUrl
 */
$confirmUrlHtml = htmlspecialchars($confirmUrl, ENT_QUOTES, 'UTF-8');

return [
    'html' => <<<HTML
<p>Hello <?= htmlspecialchars($userName) ?>,</p>

<p>Thank you for registering with us.</p>

<p>Please confirm your email by clicking the link below:</p>

<p><a href="$confirmUrlHtml">Confirm Email</a></p>

<p>If you did not request this, please ignore this message.</p>

<hr>
<p>This message was sent to <?= htmlspecialchars($userEmail) ?>.</p>
HTML,

    'text' => <<<TEXT
Hello {$userName},

Thank you for registering with us.

Please confirm your email by visiting this link:
{$confirmUrl}

If you did not request this, just ignore this message.

This message was sent to {$userEmail}.
TEXT
];
