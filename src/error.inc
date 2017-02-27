<?php

require __DIR__.'/Core/ShutdownCallbackInterface.php';

require __DIR__.'/Core/ErrorHandler.php';
$h = \Peraleks\ErrorHandler\Core\ErrorHandler::instance(__DIR__.'/config.php');
