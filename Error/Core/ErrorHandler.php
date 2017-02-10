<?php
declare(strict_types=1);

namespace MicroMir\Error\Core;

class ErrorHandler
{
    static private $instance;

    private $helper;

    private $settingsFile;

    private function __construct($settingsFile = null)
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'error']);
        set_exception_handler([$this, 'exception']);
        register_shutdown_function([$this, 'shutdown']);
        $this->settingsFile = $settingsFile;
    }

    static public function instance($settingsFile = null)
    {
        return self::$instance ?? self::$instance = new self($settingsFile);
    }

    public function error($code, $message, $file, $line)
    {
        $this->handle(
            new ErrorObject(new \ErrorException($message, $code, $code, $file, $line),'error handler')
        );
        return true;
    }

    public function exception($obj)
    {
        $this->handle(new ErrorObject($obj, 'exception handler'));
    }

    public function shutdown()
    {
        if ($e = error_get_last()) {
            $this->handle(
                new ErrorObject(
                    new \ErrorException($e['message'], $e['type'], $e['type'], $e['file'], $e['line']),
                    'shutdown function'
                )
            );
        }
        \d::m();
    }

    private function handle(ErrorObject $obj)
    {
        $this->helper
            ?: $this->helper = new Helper($this->settingsFile);
        $this->helper->handle($obj);
    }
}
