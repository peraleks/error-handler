<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;

use Peraleks\ErrorHandler\Core\ConfigInterface;
use Peraleks\ErrorHandler\Core\ErrorObject;
use Peraleks\ErrorHandler\Core\ShutdownCallbackInterface;

abstract class AbstractNotifier
{
    protected $errorObject;

    protected $configObject;

    protected $errorHandler;

    protected $traceHandlerClass = '';

    protected $finalStringError;

    public function __construct(
        ErrorObject $errorObject,
        ConfigInterface $configObject,
        ShutdownCallbackInterface $errorHandler
    ) {
    
        $this->errorObject = $errorObject;
        $this->configObject = $configObject;
        $this->errorHandler = $errorHandler;
        $this->prepare();
        $this->finalStringError = $this->ErrorToString($this->TraceToString($this->traceHandlerClass));
    }

    abstract protected function prepare();

    abstract public function notify();

    abstract protected function ErrorToString(string $trace): string;

    protected function TraceToString(string $handlerClass): string
    {
        $err = $this->errorObject;
        $con = $this->configObject;

        if ('' == $handlerClass) return '';

        if (0 != ($con->get('handleTrace') & $err->getCode())) {

            if ($con->get('phpNativeTrace')) return $err->getTraceAsString();

            $handler = new $handlerClass($err->getTrace(), $con);
            return  $handler->getTrace();
        }
        return '';
    }
}
