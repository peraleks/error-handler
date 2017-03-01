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
        'enabled'     => E_ALL,
        'handleTrace' => $trace,
        'simpleTrace' => true,
        'console'     => 'log',
    ],

    \Peraleks\ErrorHandler\Notifiers\TailNotifier::class => [
//        'enabled'      => E_ALL,
        'handleTrace'  => $trace,
        'simpleTrace'  => true,
        'stringLength' => 80,
        'file'         => $_SERVER['DOCUMENT_ROOT'].'/tail_error.log',
    ],
];