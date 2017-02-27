<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

class ErrorHandler implements ShutdownCallbackInterface
{
    static private $instance;

    private $helper;

    private $configFile;

    private $callbackData;

    private $errorCallbacks;

    private $userCallbacks;

    private function __construct($configFile = null)
    {
        set_error_handler([$this, 'error']);
        set_exception_handler([$this, 'exception']);
        register_shutdown_function([$this, 'shutdown']);
        $this->configFile = $configFile;
    }

    public static function instance($configFile = null)
    {
        return self::$instance ?? self::$instance = new self($configFile);
    }

    public function error($code, $message, $file, $line)
    {
        $this->exception(new \ErrorException($message, $code, $code, $file, $line), 'error handler');
        return true;
    }

    public function exception($obj, $handler = 'exception handler')
    {
        $this->helper ?: $this->helper = new Helper($this->configFile, $this);
        $this->helper->handle(new ErrorObject($obj, $handler));
    }

    public function shutdown()
    {
        if ($this->userCallbacks) {
            $this->invokeCallbacks($this, $this->userCallbacks);
        }

        if ($e = error_get_last()) {
            $this->exception(
                new \ErrorException($e['message'], $e['type'], $e['type'], $e['file'], $e['line']),
                'shutdown function'
            );
        }
        if ($this->errorCallbacks) {
            $this->invokeCallbacks($this->helper, $this->errorCallbacks, $this->callbackData);
        }
//        \d::m();
    }

    private function invokeCallbacks($handlerObj, $callbacks, $data = null)
    {
        try {
            set_error_handler([$handlerObj, 'error']);
            foreach ($callbacks as $callback) {
                call_user_func($callback, $data);
            }
            restore_error_handler();
        } catch (\Throwable $e) {
            $handlerObj->exception($e);
        }
    }

    public function addErrorCallbackData(string $key, $value)
    {
        $this->callbackData[$key][] = $value;
    }

    public function addErrorCallback(callable $callback)
    {
        $this->errorCallbacks[] = $callback;
    }

    public function addUserCallback(callable $callback)
    {
        $this->userCallbacks[] = $callback;
    }
}
