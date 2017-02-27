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

    protected $traceHandlerClass;

    protected $renderedError;

    public function __construct(
        ErrorObject $errorObject,
        ConfigInterface $configObject,
        ShutdownCallbackInterface $errorHandler
    ) {
    
        $this->errorObject = $errorObject;
        $this->configObject = $configObject;
        $this->errorHandler = $errorHandler;
        $this->prepare();
        $this->renderedError = $this->renderError($this->renderTrace($this->traceHandlerClass));
    }

    abstract protected function prepare();

    abstract public function notify();

    abstract protected function renderError(string $trace): string;

    protected function renderTrace(string $handlerClass): string
    {
        if (0 != ($this->configObject->get('handleTrace') & $this->errorObject->getCode())) {
            $handler = new $handlerClass($this->errorObject->getTrace(), $this->configObject);
            return  $handler->getTrace();
        }
        return '';
    }
}
