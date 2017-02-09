<?php
declare(strict_types=1);

namespace MicroMir\Error\Core;

class Helper
{
    private $settings;

    public function __construct($settingsFile)
    {
        try {
            set_error_handler([$this, 'internalErrorHandler']);
            $this->settings = new SettingsObject($settingsFile);
            restore_error_handler();
        } catch (\Throwable $e) {
            $this->internalErrorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), '', get_class($e));
        }
    }

    public function handle(ErrorObject $obj)
    {
        $code = $obj->getCode();
        $errorReporting = $this->settings ? $this->settings->getErrorReporting() : E_ALL;
        if ($code !== ($code & $errorReporting)) return;

        $this->notify($obj);
        if ($code == E_RECOVERABLE_ERROR) exit();
    }

    private function notify($obj)
    {
        try {
            set_error_handler([$this, 'internalErrorHandler']);
            foreach ($this->settings->getNotifiers() as $notifierClass => ${0}) {
                $this->settings->setNotifierClass($notifierClass);
                new $notifierClass($obj, $this->settings);
            }
            restore_error_handler();
        } catch (\Throwable $e) {
            $this->internalErrorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), '', get_class($e));
        }
    }

    public function internalErrorHandler($code, $message, $file, $line, $c, $type = null)
    {
        if (!$type){
            $type = ErrorObject::$codeName[$code] ?? 'unknown';
        }
        include dirname(__DIR__).'/View/500.php';
    }
}