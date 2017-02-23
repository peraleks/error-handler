<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

require 'ShutdownCallbackInterface.php';

class ErrorHandler implements ShutdownCallbackInterface
{
    static private $instance;

    private $helper;

    private $settingsFile;

    private $callbackData;

    private $callbacks;

    private function __construct($settingsFile = null)
    {
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
        $this->passToHelper(
            new ErrorObject(new \ErrorException($message, $code, $code, $file, $line),'error handler')
        );
        return true;
    }

    public function exception($obj)
    {
        $this->passToHelper(new ErrorObject($obj, 'exception handler'));
    }

    public function shutdown()
    {
        if ($e = error_get_last()) {
            $this->passToHelper(
                new ErrorObject(
                    new \ErrorException($e['message'], $e['type'], $e['type'], $e['file'], $e['line']),
                    'shutdown function'
                )
            );
        }
        if ($this->callbacks) {
            foreach ($this->callbacks as $callback) {
                try {
                    set_error_handler([$this->helper, 'internalErrorHandler']);
                    call_user_func($callback, $this->callbackData);
                    restore_error_handler();
                } catch (\Throwable $e) {
                    $this->helper->internalErrorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), '', get_class($e));
                }
            }
        }
//        \d::m();
    }

    private function passToHelper(ErrorObject $obj)
    {
        $this->helper
            ?: $this->helper = new Helper($this->settingsFile, $this);
        $this->helper->handle($obj);
    }

    public function addToCallbackDataArray(string $key, $value)
    {
        $this->callbackData[$key][] = $value;
    }

    public function addCallback(callable $callback)
    {
        $this->callbacks[] = $callback;
    }
}
