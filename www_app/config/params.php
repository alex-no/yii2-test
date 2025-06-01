<?php
$hostName = !empty(\Yii::$app->request) ? Yii::$app->request->getHostName() : $_SERVER['SERVER_NAME'];
$currentDomain = $hostName ?? 'localhost';

$params = [
    'adminEmail' => 'admin@4n.com.ua',
    'senderEmail' => 'admin@4n.com.ua',
    'senderName' => 'Alex mailer',
    'JwtSecret' => 'MySuperSecretKey',
    'hiddenFields' => [
        \app\models\User::class => [
            'password',
            'auth_key',
            'remember_token',
        ],
        // \app\models\Admin::class => [
        //     'password',
        // ],
    ],
    'advActive' => [
        // Common AdvActiveRecord config
        \app\components\i18n\AdvActiveRecord::class => [
            'localizedPrefixes' => '@@',
            'isStrict' => true,
        ],
        // Common AdvActiveQuery config
        \app\components\i18n\AdvActiveQuery::class => [
            'localizedPrefixes' => '@@',
        ],
        // Personal Model PetType config
        // \app\models\PetType::class => [
        //     'localizedPrefixes' => '##',
        // ],
    ],

    'payment.driver' => 'liqpay',
    // List of available payment systems
    'payment.drivers' => [
        'liqpay' => [
            'class' => \app\components\payment\drivers\LiqPayDriver::class,
            'config' => [
                'publicKey'  => null, // Set your public key here
                'privateKey' => null, // Set your private key here
                'callbackUrl' => 'https://' . $currentDomain . '/api/payments/handle',
                'resultUrl' => 'https://' . $currentDomain . '/html/payments/success',
            ],
        ],
        // 'paypal' => [...],
        // 'stripe' => [...],
    ],
];

return yii\helpers\ArrayHelper::merge(
    $params,
    is_file(__DIR__ . '/params-secure.php') ? require __DIR__ . '/params-secure.php' : []
);
