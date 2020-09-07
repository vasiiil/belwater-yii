<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request'      => [
            'parsers'              => [
                'application/json' => \yii\web\JsonParser::class,
            ],
            'enableCsrfValidation' => false,
            'class'                => 'yii\web\Request',
            'baseUrl' => '',
        ],
//        'response'     => [
//            'format'        => yii\web\Response::FORMAT_JSON,
//            'charset'       => 'UTF-8',
//            'on beforeSend' => function($event)
//            {
//                $event->sender->headers->add('Access-Control-Allow-Origin', '*');
//                $event->sender->headers->add('Access-Control-Allow-Headers', '*');
//            },
//            // ...
//        ],
    ],
    'params' => $params,
];
