<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

use Peraleks\ErrorHandler\Notifiers\AbstractNotifier;

class Helper
{
    private $config;

    private $errorHandler;

    private $exit;

    public function __construct($configFile, $errorHandler)
    {
        $this->errorHandler = $errorHandler;

        try {
            set_error_handler([$this, 'error']);
            $this->config = new ConfigObject($configFile);
            restore_error_handler();
        } catch (\Throwable $e) {
            $this->exception($e);
        }
    }

    public function handle(ErrorObject $obj)
    {
        if (!$this->config) {
            return;
        }
        $code = $obj->getCode();

        /* обработка параметра ERROR_REPORTING (файла настроек) */
        if (0 == ($code & $this->config->getErrorReporting())) {
            return;
        }

        $this->notify($obj, $this->config, $this->errorHandler);

        /* воспроизводим стандартное поведение PHP для ошибок
         * E_RECOVERABLE_ERROR,  E_USER_ERROR (скрипт должен быть остановлен,
         * если пользовательский обработчик не был определён)*/
        if ($code & (E_RECOVERABLE_ERROR | E_USER_ERROR)) {
            exit;
        }
    }

    private function notify(ErrorObject $obj, ConfigInterface $config, $errorHandler)
    {
        $exit = null;
        try {
            set_error_handler([$this, 'error']);
            foreach ($config->getNotifiers() as $notifierClass => ${0}) {
                $config->setNotifierClass($notifierClass);

                /* проверяем для конкретного Notifier надо ли обрабатывать ошибку */
                if (0 == ($config->get('enabled') & $obj->getCode())) {
                    continue;
                }

                /* @var $notifier AbstractNotifier */
                $notifier = new $notifierClass($obj, $config, $errorHandler);
                $exit = $notifier->notify();
            }
            restore_error_handler();
        } catch (\Throwable $e) {
            $this->exception($e);
        }
        if ($exit) {
            $this->exit = true;
            exit;
        }
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

    public function exception(\Throwable $obj)
    {
        if ($obj instanceof \ErrorException) {
            $type = ErrorObject::$codeName[$obj->getCode()] ?? 'unknown';
        } else {
            $type = get_class($obj);
        }
        $file = $obj->getFile();
        $line = $obj->getLine();
        $message = $obj->getMessage();
        include dirname(__DIR__).'/View/500.php';
    }
}
