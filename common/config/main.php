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
        'request'      => [
            'parsers'              => [
                'application/json' => \yii\web\JsonParser::class,
            ],
            'enableCsrfValidation' => false,
            'class'                => 'yii\web\Request',
        ],
        'response'     => [
            'format'        => yii\web\Response::FORMAT_JSON,
            'charset'       => 'UTF-8',
            'on beforeSend' => function($event)
            {
                $event->sender->headers->add('Access-Control-Allow-Origin', '*');
                $event->sender->headers->add('Access-Control-Allow-Headers', '*');
            },
            // ...
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
            'rules'           => [
                "/telegram/<bot:\d+:\w+>"                                                       => "telegram/index/",
                "/actions/edit/<id_action:\d+>"                                                 => "actions/edit/",
                "/social/reviews/create\/?"                                                     => "social/reviews-create/",
                "/social/reviews/moderation\/?"                                                 => "social/reviews-moderation/",
                "/social/reviews/wow\/?"                                                        => "social/reviews-wow/",
                "/social/reviews/gid\/?"                                                        => "social/reviews-gid/",
                "/social/reviews/edit/<id_review:\d+>"                                          => "social/reviews-edit/",
                "/social/reviews/event/<id_review:\d+>"                                         => "social/reviews-event/",
                "/push-notification/template/<id_notification:\d+>"                             => "push-notification/template/",
                //"/support/?" => "support-topics/index",
                "/support/<name:\w+>"                                                           => "support-<name>/index",
                "/support/<name:\w+>/<id:\d+>"                                                  => "support-<name>/index",
                "/support/<name:\w+>/<method:\w*>"                                              => "support-<name>/<method>",
                "/support/<name:\w+>/<method:(topic|contact)>/<id:\d+>"                         => "support-<name>/index",
                "/support/<name:\w+>/search/<search:.+>"                                        => "support-<name>/index",
                "/support/<name:\w+>/<id:\d*>/search/<search:.+>"                               => "support-<name>/index",
                "/support/<name:\w+>/<action:(update|delete|get|set)>-<method:[\w-]+>/<id:\d+>" => "support-<name>/<action>-<method>",
                "/support/<name:\w+>/<action:(get|upload|update|set|send)>-<method:[\w-]+>"     => "support-<name>/<action>-<method>",
            ],
        ],
        'db'           => [
            'class'               => 'yii\db\Connection',
            'dsn'                 => "mysql:host=localhost;dbname=belwater_db",
            'username'            => 'root',//'sql_user_labirint',
            'password'            => '',//'sLd5Pbw7MH19',
            'charset'             => 'utf8',
            'enableSchemaCache'   => true,
            'schemaCacheDuration' => 60 * 24,
            'schemaCache'         => 'cache',
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
                'username'   => 'nikitin-vasya95@yandex.ru',
                'password'   => 'skpdhcymshrgmckm',
                //                'host' => 'smtp.gmail.ru',
                //                'username' => 'nikitinvdimvoch@gmail.com',
                //                'password' => '2202vasiiil1995',
                'port'       => '465',
                'encryption' => 'ssl',
                //                'extraParams' => null,
            ],
        ],
    ],
    'params'     => $params,
];
