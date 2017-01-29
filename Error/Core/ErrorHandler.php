<?php
declare(strict_types=1);

namespace MicroMir\Error\Core;

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

    public function error($code, $message, $file, $line)
    {
        $this->handle(
            new ErrorObject(
                new \ErrorException(
                    $message,
                    $code,
                    $code,
                    $file,
                    $line
                ), 'error handler'
            )
        );
        return true;
    }

    public function exception($obj)
    {
        $this->handle(new ErrorObject($obj, 'exception handler'));
    }

    public function shutdown()
    {
        if (${0} = error_get_last()) {
            $this->handle(
                new ErrorObject(
                    new \ErrorException(
                        ${0}['message'],
                        ${0}['type'],
                        ${0}['type'],
                        ${0}['file'],
                        ${0}['line']
                    ), 'shutdown function'
                )
            );
        }
    }

    private function handle(ErrorObject $obj)
    {
        $this->helper
            ?: $this->helper = new Helper(new SettingsObject($this->settingsFile, ""));
        $this->helper->handle($obj);
    }
}
