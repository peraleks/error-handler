<?php

$error500 = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

return [

    \Peraleks\ErrorHandler\Notifiers\FileNotifier::class => [
        'enabled'        => E_ALL,
        'handleTrace'    => $trace,
        'phpNativeTrace' => true,
        'timeFormat'     => 'd-M-o H:i:s O',
        'file'           => $_SERVER['DOCUMENT_ROOT'].'/error_php.log',
    ],

    \Peraleks\ErrorHandler\Notifiers\ServerErrorNotifier::class => [
        'enabled'       => $error500,
        'ignoreLogType' => true,
        'header'        => 'HTTP/1.1 500 Internal Server Error',
        'includeFile'   => '',
    ],
];
