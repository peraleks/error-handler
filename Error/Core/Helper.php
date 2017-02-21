<?php
declare(strict_types=1);

namespace MicroMir\Error\Core;

use MicroMir\Error\Notifiers\AbstractNotifier;

class Helper
{
    private $settings;

    private $errorHandler;

    public function __construct($settingsFile, $errorHandler)
    {
        $this->errorHandler = $errorHandler;
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
        if (!$this->settings) return;
        $code = $obj->getCode();

        /* обработка параметра ERROR_REPORTING (файла настроек) */
        if (0 == ($code & $this->settings->getErrorReporting())) return;

        $this->notify($obj, $this->settings, $this->errorHandler);

        /* воспроизводим стандартное поведение PHP для ошибок
         * E_RECOVERABLE_ERROR,  E_USER_ERROR (скрипт должен быть остановлен,
         * если пользовательский обработчик не был определён)*/
        if ($code & (E_RECOVERABLE_ERROR | E_USER_ERROR)) exit;
    }

    private function notify(ErrorObject $obj, SettingsInterface $settings, $errorHandler)
    {
        try {
            set_error_handler([$this, 'internalErrorHandler']);
            foreach ($settings->getNotifiers() as  $notifierClass => ${0}) {
                $settings->setNotifierClass($notifierClass);

                /* проверяем надо ли обрабатывать ошибку для конкретного Notifier */
                if (0 == ($settings->get('enabledFor') & $obj->getCode())) continue;

                /* @var $notifier AbstractNotifier */
                $notifier = new $notifierClass($obj, $settings, $errorHandler);
                $notifier->notify();
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