<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

use Peraleks\ErrorHandler\Notifiers\AbstractNotifier;

class Helper
{
    /**
     * @var ConfigObject
     */
    private $configObject;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var SelfErrorHandler
     */
    private $selfErrorHandler;

    /**
     * Флаг означает, что фатальная ошибка
     * произошла внутри обработчика.
     *
     * @var bool
     */
    private $innerShutdownFatal = false;

    /**
     * Helper constructor.
     *
     * @param string $configFile
     * @param ErrorHandler $errorHandler
     */
    public function __construct(string $configFile, ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
        $this->configFile = $configFile;
    }

    /**
     * Инстанцирует ConfigObject
     *
     * Вызов должен производится извне, а не из конструктора, так как
     * фатальная ошибка в конфигурационном файле приведёт к тому,
     * что Helper не будет инстанцирован.
     */
    public function createConfigObject()
    {
        $this->innerShutdownFatal = true;
        try {
            set_error_handler([$this, 'error']);

            $this->configObject = new ConfigObject($this->configFile);

        } catch (\Throwable $e) {
            $this->exception($e);
        } finally {
            restore_error_handler();
        }
        $this->innerShutdownFatal = false;
    }

    public function handle(\Throwable $e, string $handler)
    {
        $eObj = new ErrorObject($e, $handler);

        if (!$this->configObject) {
            $this->exception($eObj);
            return;
        }

        $code = $eObj->getCode();

        /* обработка параметра ERROR_REPORTING (файл конфигурации) */
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
                $this->exception($eObj);
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

    public function error($code, $message, $file, $line)
    {
        $this->exception(new \ErrorException($message, $code, $code, $file, $line));
        return true;
    }

    public function exception($e)
    {
        $this->selfErrorHandler
            ?: $this->selfErrorHandler = new SelfErrorHandler($this->configObject);
        $this->selfErrorHandler->report($e);
    }
}
