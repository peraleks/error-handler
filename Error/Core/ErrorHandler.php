<?php

namespace MicroMir\Error\Core;

use MicroMir\Error\ErrorObjects\AbstractErrorObject;
use MicroMir\Error\ErrorObjects\ErrorObject;
use MicroMir\Error\ErrorObjects\ExceptionObject;
use MicroMir\Error\ErrorObjects\ShutdownObject;

class ErrorHandler
{
    static private $instance;

    private $helper;

    private $settingsFile;

    private function __construct($settingsFile)
    {
//        ini_set('display_errors', false);
        error_reporting(E_ALL);
        set_error_handler([$this, 'error']);
        set_exception_handler([$this, 'exception']);
        register_shutdown_function([$this, 'shutdown']);
        $this->settingsFile = $settingsFile;
    }

    static public function instance($settingsFile = null)
    {
        self::$instance
            ?: self::$instance = new self($settingsFile);
        return self::$instance;
    }

    public function error()
    {
        $this->handle(new ErrorObject(debug_backtrace()));
        return true;
    }

    public function exception()
    {
        $this->handle(new ExceptionObject(debug_backtrace()));
    }

    public function shutdown()
    {
        if (${0} = error_get_last()) $this->handle(new ShutdownObject(${0}));
    }

    private function handle(AbstractErrorObject $obj)
    {
        if (!$this->helper) {
            $this->helper = new Helper(new SettingsObject($this->settingsFile, __CLASS__));
        }
        $this->helper->handle($obj);
    }
}
