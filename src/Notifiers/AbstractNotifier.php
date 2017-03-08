<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;

use Peraleks\ErrorHandler\Core\ConfigInterface;
use Peraleks\ErrorHandler\Core\ErrorHandler;
use Peraleks\ErrorHandler\Core\ErrorObject;

abstract class AbstractNotifier
{
    protected $errorObject;

    protected $configObject;

    protected $errorHandler;

    protected $finalStringError;

    public function __construct(
        ErrorObject $errorObject,
        ConfigInterface $configObject,
        ErrorHandler $errorHandler
    ) {
    
        $this->errorObject = $errorObject;
        $this->configObject = $configObject;
        $this->errorHandler = $errorHandler;
        $this->prepare();
        $this->finalStringError = $this->ErrorToString($this->TraceToString($this->getTraceHandlerClass()));
    }

    abstract protected function prepare();

    abstract protected function getTraceHandlerClass(): string;

    abstract public function notify();

    abstract protected function ErrorToString(string $trace): string;

    protected function TraceToString(string $traceHandlerClass): string
    {
        $err = $this->errorObject;
        $con = $this->configObject;

        if ('' == $traceHandlerClass) return '';

        if (0 != ($con->get('handleTrace') & $err->getCode())) {

            if ($con->get('phpNativeTrace')) return $err->getTraceAsString();

            $handler = new $traceHandlerClass($err->getTrace(), $con);
            return  $handler->getTrace();
        }
        return '';
    }
}
