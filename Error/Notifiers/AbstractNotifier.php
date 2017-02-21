<?php
declare(strict_types=1);

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\Core\SettingsInterface;
use MicroMir\Error\Core\ErrorObject;
use MicroMir\Error\Core\ShutdownCallbackInterface;

abstract class AbstractNotifier
{
    protected $errorObject;

    protected $settingsObject;

    protected $errorHandler;

    public function __construct(ErrorObject $errorObject,
                                SettingsInterface $settingsObject,
                                ShutdownCallbackInterface $errorHandler)
    {
        $this->errorObject = $errorObject;
        $this->settingsObject = $settingsObject;
        $this->errorHandler = $errorHandler;
        $this->prepare();
    }

    abstract protected function prepare();

    abstract public function notify();
}