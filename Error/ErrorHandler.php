<?php

namespace MicroMir\Error;

use MicroMir\Error\ErrorObjects\AbstractErrorObject;
use MicroMir\Error\ErrorObjects\ErrorObject;
use MicroMir\Error\ErrorObjects\ExceptionObject;
use MicroMir\Error\ErrorObjects\FatalErrorObject;
use MicroMir\Error\Notifiers\HttpNotifier;

class ErrorHandler
{
    static private $instance;

    private $headerMessages = [];

    private $headerMessagesDefault = [];

    private $trace = true;

    private $log = true;

    private $http = true;

    private function __construct()
    {
        set_error_handler([$this, 'error']);

        set_exception_handler([$this, 'exception']);

        register_shutdown_function([$this, 'fatalError']);

        $this->headerMessagesDefault['header']    = '500 Internal Server Error';
        $this->headerMessagesDefault['message'][] = 'Сервер отдыхает. Зайдите позже.';
        $this->headerMessagesDefault['message'][] = "Don't worry! Chip 'n Dale Rescue Rangers";
   }

    static public function instance()
    {
        self::$instance
            ?: self::$instance = new self;
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
        $trace   = $obj->getTrace();
        $message = $obj->getMessage();

        if (is_string($traceNumber)) {
            $arr = explode('::', $traceNumber);
            $file = $arr[0];
            $line = isset($arr[1]) ? $arr[1] : '';
            $traceNumber = 0;

        } elseif (isset($trace[$traceNumber]['file'])) {
            $file = $trace[$traceNumber]['file'];
            $line = $trace[$traceNumber]['line'];
        } else {
            $file = '';
            $line = '<-';
        }
        $this->traceHandler($trace, $traceNumber);

        $this->notify(
            $obj->getCode(),                    // code
            'Micro_Exception',                  // name
            $message['displayError'],           // message
            $message['logError'],               // log message
            $file,
            $line
        );
    }

    public function fatalError()
    {
        $this->handle(new FatalErrorObject(debug_backtrace()));
    }

    private function handle(AbstractErrorObject $obj)
    {
        if ($this->http)  new HttpNotifier($obj);
        if ($this->log)  new LogNotifier($obj);
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


    private function notify($code, $name, $message, $logMess, $file, $line)
    {
        include(__DIR__.'/notify.php');
    }


    private function traceHandler($trace, int $traceNumber = 0)
    {
        $thisClass = __CLASS__;

        include(__DIR__.'/trace.php');

    }
}
