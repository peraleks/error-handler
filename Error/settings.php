<?php

define('DEVELOPMENT_MODE', true);

return [
    'ERROR_REPORTING' => E_ALL,

    'APP_DIR' => $_SERVER['DOCUMENT_ROOT'],

    'DEVELOPMENT_MODE_CONSTANT' => 'DEVELOPMENT_MODE',

    'DEV' => [
        \MicroMir\Error\Notifiers\HtmlNotifier::class => [
            'handleTrace'   => true,
//            'minimizeTrace' => true,
            'stringLength'  => 80,
            'fontSize'      => 15,
        ],
        \MicroMir\Error\Notifiers\TailNotifier::class => [
            'handleTrace'   => true,
            'file' => $_SERVER['DOCUMENT_ROOT'].'/Tests/tail/tail_error.log',
        ],
    ],

    'PROD' => [

    ],

    'CLI'  => [
        \MicroMir\Error\Notifiers\CliNotifier::class => [
//            'handleTrace'   => true,
        ],

    ],
];