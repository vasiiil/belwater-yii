<?php
$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
return [
    'aliases'    => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache'        => [
            'class' => 'yii\caching\FileCache',
        ],
        'session'      => [
            'class' => 'yii\web\CacheSession',
            'name'  => 'YiiPhpSesId',
        ],
        'user'         => [
            'loginUrl'        => "/login",
            'identityClass'   => \common\models\User::class,
            'enableAutoLogin' => true,
            'authTimeout'     => 3600 * 24,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
        ],
        'mailer'       => [
            'class'                    => 'yii\swiftmailer\Mailer',
            'enableSwiftMailerLogging' => true,
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport'         => false,
            'viewPath'                 => '@common/mail',
            'transport'                => [
                'class'      => Swift_SmtpTransport::class,
                'host'       => 'smtp.yandex.ru',
                'username'   => $params['senderEmail'],
                'password'   => $params['senderEmailPassword'],
                'port'       => '465',
                'encryption' => 'ssl',
                //                'extraParams' => null,
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
        ],
    ],
    'params'     => $params,
];
