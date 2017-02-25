<?php

define('DEVELOPMENT_MODE', true);

const TRACE = E_ERROR | E_RECOVERABLE_ERROR;

return [
    'ERROR_REPORTING' => E_ALL,

    'DEVELOPMENT_MODE_CONSTANT' => 'DEVELOPMENT_MODE',

    'APP_DIR' => $_SERVER['DOCUMENT_ROOT'],

    'DEV' => [
        \Peraleks\ErrorHandler\Notifiers\HtmlNotifier::class => [
            'enabledFor'     => E_ALL,
            'deferredView'   => true,
            'hideView'       => true,
            'handleTraceFor' => TRACE,
            'hideTrace'      => true,
            'fontSize'       => 15,
            'stringLength'   => 80,
            'tooltipLength'  => 1000,
            'arrayLevel'     => 2,
        ],
        \Peraleks\ErrorHandler\Notifiers\TailNotifier::class => [
//            'enabledFor'  => E_ALL,
            'handleTrace' => TRACE,
            'file'        => $_SERVER['DOCUMENT_ROOT'].'/tail_error.log',
        ],
    ],

    'PROD' => [

    ],

    'CLI' => [
        \Peraleks\ErrorHandler\Notifiers\CliNotifier::class => [
            'enabledFor' => E_ALL,
//            'handleTrace'   => TRACE,
        ],

    ],
];
