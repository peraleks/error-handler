<?php

return [

    \Peraleks\ErrorHandler\Notifiers\HtmlNotifier::class => [
        'enabled'       => E_ALL,
        'deferredView'  => true,
        'hideView'      => true,
        'handleTrace'   => $trace,
//        'simpleTrace'   => true,
        'hideTrace'     => true,
        'fontSize'      => 15,
        'stringLength'  => 80,
        'tooltipLength' => 1000,
        'arrayLevel'    => 2,
    ],

    \Peraleks\ErrorHandler\Notifiers\BrowserConsoleNotifier::class => [
        'enabled'        => E_ALL,
        'deferredView'   => true,
        'handleTrace'    => $trace,
//        'phpNativeTrace' => true,
        'console'        => 'log',
    ],

    \Peraleks\ErrorHandler\Notifiers\TailNotifier::class => [
        'enabled'      => E_ALL,
        'handleTrace'  => $trace,
        'simpleTrace'  => true,
        'stringLength' => 80,
        'timeFormat'   => 'H:i:s',
        'file'         => $_SERVER['DOCUMENT_ROOT'].'/tail_error.log',
    ],

    \Peraleks\ErrorHandler\Notifiers\FileNotifier::class => [
        'enabled'        => E_ALL,
        'handleTrace'    => $trace,
//        'phpNativeTrace' => true,
        'timeFormat'     => 'd-M-o H:i:s O',
        'file'           => $_SERVER['DOCUMENT_ROOT'].'/error_php.log',
    ],
];