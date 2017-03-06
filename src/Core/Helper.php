<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

use Peraleks\ErrorHandler\Notifiers\AbstractNotifier;

class Helper
{
    private $configObject;

    private $errorHandler;

    private $selfErrorHandler;

    private $exit;

    private $innerShutdownFatal;

    public function __construct($configFile, $errorHandler)
    {
        $this->errorHandler = $errorHandler;

        try {
            set_error_handler([$this, 'error']);
            $this->configObject = new ConfigObject($configFile);
            restore_error_handler();
        } catch (\Throwable $e) {
            $this->exception($e);
        }
    }

    public function handle(\Throwable $e, string $handler)
    {
        if (!$this->configObject) {
            return;
        }
        $eObj = new ErrorObject($e, $handler);

        $code = $eObj->getCode();

        /* обработка параметра ERROR_REPORTING (файла настроек) */
        if (0 == ($code & $this->configObject->getErrorReporting())) {
            return;
        }

        $this->notify($eObj, $this->configObject, $this->errorHandler);

        /* воспроизводим стандартное поведение PHP для ошибок
         * E_RECOVERABLE_ERROR,  E_USER_ERROR (скрипт должен быть остановлен,
         * если пользовательский обработчик не был определён)*/
        if ($code & (E_RECOVERABLE_ERROR | E_USER_ERROR)) {
            exit;
        }
    }

    private function notify(ErrorObject $eObj, ConfigInterface $configObject, $errorHandler)
    {
        $this->innerShutdownFatal = true;
        $exit = null;
        foreach ($configObject->getNotifiers() as $notifierClass => ${0}) {
            try {
                set_error_handler([$this, 'error']);
                $configObject->setNotifierClass($notifierClass);

                /* проверяем для конкретного Notifier надо ли обрабатывать ошибку */
                if (0 == ($configObject->get('enabled') & $eObj->getCode())) {
                    continue;
                }

                /* @var $notifier AbstractNotifier */
                $notifier = new $notifierClass($eObj, $configObject, $errorHandler);

                if (!$notifier instanceof AbstractNotifier) {
                    trigger_error(
                        $notifierClass.' must extend '.AbstractNotifier::class,
                        E_USER_ERROR
                    );
                    continue;
                }
                $exit = $notifier->notify();

            } catch (\Throwable $e) {
                $this->exception($e);
            } finally {
                restore_error_handler();
            }
        }
        $this->innerShutdownFatal = false;
        if ($exit) {
            $this->exit = true;
            exit;
        }
    }

    public function getInnerShutdownFatal()
    {
        return $this->innerShutdownFatal;
    }

    public function exitStatus()
    {
        return $this->exit;
    }

    public function error($code, $message, $file, $line)
    {
        $this->exception(new \ErrorException($message, $code, $code, $file, $line));
        return true;
    }

    public function exception(\Throwable $e)
    {
        $this->selfErrorHandler
            ?: $this->selfErrorHandler = new SelfErrorHandler($this->configObject);
        $this->selfErrorHandler->report($e);
    }
}
