<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;

use Peraleks\ErrorHandler\Core\SettingsInterface;
use Peraleks\ErrorHandler\Core\ErrorObject;
use Peraleks\ErrorHandler\Core\ShutdownCallbackInterface;

abstract class AbstractNotifier
{
    protected $errorObject;

    protected $settingsObject;

    protected $errorHandler;

    public function __construct(
        ErrorObject $errorObject,
        SettingsInterface $settingsObject,
        ShutdownCallbackInterface $errorHandler
    ) {
    
        $this->errorObject = $errorObject;
        $this->settingsObject = $settingsObject;
        $this->errorHandler = $errorHandler;
        $this->prepare();
    }

    abstract protected function prepare();

    abstract public function notify();
}
