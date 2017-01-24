<?php

namespace MicroMir\Error;

use MicroMir\Error\ErrorObjects\AbstractErrorObject;
use MicroMir\Error\ErrorObjects\CustomExceptionObject;
use MicroMir\Error\ErrorObjects\ErrorObject;
use MicroMir\Error\ErrorObjects\ExceptionObject;
use MicroMir\Error\ErrorObjects\ShutdownObject;

class ErrorHandler
{
    static private $instance;

    private $settings;

    private $settingsFile;

    private function __construct($settingsFile)
    {
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
        return true;
    }


    public function microException($obj, $traceNumber)
    {
        $this->handle(new CustomExceptionObject(debug_backtrace()));
        return true;
    }

    public function shutdown()
    {
        if ($error = error_get_last()) {
            ob_end_clean();
            $this->handle(new ShutdownObject($error));
        }
    }

    private function handle(AbstractErrorObject $obj)
    {
        $this->settings ?: $this->settings = new Settings($this->settingsFile);

        foreach ($this->settings->getNotifiers() as $notifierClass => $arr) {
            $this->settings->setNotifierClass($notifierClass);
            new $notifierClass($obj, $this->settings);
        }
    }


    public function setHeaderMessage($array = null)
    {
        if ($array === null) {
            $this->errorParam('empty parametrs');
            return $this;
        }
        if (!array_key_exists('marker', $array)) {
            $this->errorParam("missing key 'marker'");
            return $this;
        }
        $this->headerMessages[$array['marker']]
            = array_merge($this->headerMessagesDefault, $array);

        return $this;
    }

    private function errorParam($params)
    {
        $deb = debug_backtrace()[1];
        $this->notify(2, 'USER_WARNING', $params, $deb['file'], $deb['line']);
    }

    private function sendHeaderMessage($phrase = '')
    {
        if (defined('MICRO_DEVELOPMENT') && MICRO_DEVELOPMENT === true) return;

        if (isset($GLOBALS['MICRO_ERROR_MARKER'])
            && isset($this->headerMessages[$GLOBALS['MICRO_ERROR_MARKER']])
        ) {
            $arr = $this->headerMessages[$GLOBALS['MICRO_ERROR_MARKER']];
        } else {
            $arr = $this->headerMessagesDefault;
        }

        $statusCode = explode(' ', $arr['header'])[0];
        $message = $arr['message'];

        if (!headers_sent()) {
            header($_SERVER['SERVER_PROTOCOL'].' '.$arr['header']);
        }
        if (defined('MICRO_ERROR_PAGE')) {
            include MICRO_ERROR_PAGE;
        } else {
            include(__DIR__.'/500.php');
        }
    }

}
