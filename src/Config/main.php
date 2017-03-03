<?php

define('DEVELOPMENT_MODE', true);

$modeConstant = 'DEVELOPMENT_MODE';


if (PHP_SAPI === 'cli') {
    $config = __DIR__.'/configCLI.php';
} elseif (defined($modeConstant) && true === constant($modeConstant)) {
    $config = __DIR__.'/configDEV.php';
} else {
    $config = __DIR__.'/configPROD.php';
}


$trace = E_ERROR | E_RECOVERABLE_ERROR;

return [
    'ERROR_REPORTING' => E_ALL,
    'APP_DIR'         => dirname(dirname(__DIR__)),
    'NOTIFIERS'       => require $config,
];

/*
E_ERROR
E_WARNING
E_PARSE
E_NOTICE
E_CORE_ERROR
E_CORE_WARNING
E_COMPILE_ERROR
E_COMPILE_WARNING
E_USER_ERROR
E_USER_WARNING
E_USER_NOTICE
E_STRICT
E_RECOVERABLE_ERROR
E_DEPRECATED
E_USER_DEPRECATED
*/