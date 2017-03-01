<?php

return [

    \Peraleks\ErrorHandler\Notifiers\CliNotifier::class => [
        'enabled'      => E_ALL,
        'handleTrace'  => $trace,
        'simpleTrace'  => true,
        'stringLength' => 80,
    ],
];
