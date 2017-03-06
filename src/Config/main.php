<?php

define('DEVELOPMENT_MODE', true);

$modeConstant = 'DEVELOPMENT_MODE';

if (PHP_SAPI === 'cli') {
    $config = __DIR__.'/configCLI.php';
} elseif (defined($modeConstant) && true === constant($modeConstant)) {
    $config = __DIR__.'/configDEV.php';
    $mode = 'dev';
} else {
    $config = __DIR__.'/configPROD.php';
}

$trace = E_ERROR | E_RECOVERABLE_ERROR;

return [
    'SELF_LOG_FILE'   => '',
    'ERROR_REPORTING' => E_ALL,
    'APP_DIR'         => 'default',
    'NOTIFIERS'       => require $config,
    'MODE'            => $mode ?? 'prod',
];


/*  0
    1 E_ERROR
    2 E_WARNING
    4 E_PARSE
    8 E_NOTICE
   16 E_CORE_ERROR
   32 E_CORE_WARNING
   64 E_COMPILE_ERROR
  128 E_COMPILE_WARNING
  256 E_USER_ERROR
  512 E_USER_WARNING
 1024 E_USER_NOTICE
 2048 E_STRICT
 4096 E_RECOVERABLE_ERROR
 8192 E_DEPRECATED
16384 E_USER_DEPRECATED
32767 E_ALL
*/
