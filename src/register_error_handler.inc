<?php

require __DIR__.'/Autoload/ClassLoader.php';
(new \Peraleks\ErrorHandler\Autoload\ClassLoader);

\Peraleks\ErrorHandler\Core\ErrorHandler::instance(__DIR__.'/Config/main.php');
