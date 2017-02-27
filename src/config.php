<?php

define('DEVELOPMENT_MODE', true);

const TRACE = E_ERROR | E_RECOVERABLE_ERROR;

return [
    'ERROR_REPORTING' => E_ALL,

    'DEVELOPMENT_MODE_CONSTANT' => 'DEVELOPMENT_MODE',

//    'APP_DIR' => dirname(__DIR__),

    'DEV' => [
        \Peraleks\ErrorHandler\Notifiers\HtmlNotifier::class => [
            'enabled'       => E_ALL,
            'deferredView'  => true,
            'hideView'      => true,
            'handleTrace'   => TRACE,
            'hideTrace'     => true,
            'fontSize'      => 15,
            'stringLength'  => 80,
            'tooltipLength' => 1000,
            'arrayLevel'    => 2,
        ],
        \Peraleks\ErrorHandler\Notifiers\TailNotifier::class => [
            'enabled'     => E_ALL,
            'handleTrace' => TRACE,
            'simpleTrace' => true,
            'file'        => $_SERVER['DOCUMENT_ROOT'].'/tail_error.log',
        ],
    ],

    'CLI' => [
        \Peraleks\ErrorHandler\Notifiers\CliNotifier::class => [
            'enabled'     => E_ALL,
            'handleTrace' => TRACE,
            'simpleTrace' => true,
        ],

    ],

    'PROD' => [

    ],


];
