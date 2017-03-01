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
