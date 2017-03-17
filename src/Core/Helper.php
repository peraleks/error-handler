<?php
/**
 * PHP error handler and debugger.
 *
 * @package   Peraleks\ErrorHandler
 * @copyright 2017 Aleksey Perevoshchikov <aleksey.perevoshchikov.n@gmail.com>
 * @license   https://github.com/peraleks/error-handler/blob/master/LICENSE.md MIT
 * @link      https://github.com/peraleks/error-handler
 */

declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

use Peraleks\ErrorHandler\Exception\ErrorHandlerException;
use Peraleks\ErrorHandler\Notifiers\AbstractNotifier;

/**
 * Class Helper
 *
 * Помощник.
 * Здесь находится весь остальной функционал контроллера обработки ошибок,
 * который оказалось возможным вынести из ErrorHandler, для снижения оверхэда.<br>
 * Регистрирует функции для обработки внутренних ошибок.
 */
class Helper
{
    /**
     * Объект конфигурации.
     *
     * @var ConfigObject
     */
    private $configObject;

    /**
     * Объект основного контроллера обработки ошибок.
     *
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * Объект внутреннего обработчика ошибок.
     *
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
     * @param string       $configFile   полное имя файла конфигурации
     * @param ErrorHandler $errorHandler объект основного контроллера обработки ошибок
     */
    public function __construct(string $configFile = null, ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
        $this->configFile = $configFile;
    }

    /**
     * Инстанцирует ConfigObject.
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

    /**
     * Запускает обработку ошибки.
     *
     * Оборачивает объект ошибки в ErrorObject.
     * Если не было ошибки в конфигурационном файле
     * запускает механизм уведомления, иначе передает
     * ErrorObject во внутренний обработчик ошибок.
     *
     * @param \Throwable $e       объект ошибки
     * @param string     $logType тип ошибки
     * @param string     $handler 'error' | 'exception' | 'shutdown'
     *                            название функции обработчика
     * @return void
     */
    public function handle(\Throwable $e, $logType = '', string $handler)
    {
        $errorObject = new ErrorObject($e, $logType, $handler);

        /* отсутствие объекта конфигурации говорит о том,
         * что в конфигурационном файле произошла ошибка.
         * Поэтому отправляем ошибку во внутренний обработчик
         * и завершаем процесс обработки */
        if (!$this->configObject) {
            $this->exception($errorObject);
            return;
        }

        $code = $errorObject->getCode();

        /* обработка параметра ERROR_REPORTING (файл конфигурации) */
        if (0 == ($code & $this->configObject->getErrorReporting())) {
            return;
        }

        $this->notify($errorObject, $this->configObject, $this->errorHandler);

        /* воспроизводим стандартное поведение PHP для ошибок
         * E_RECOVERABLE_ERROR, E_USER_ERROR (выполнение скрипта будет прервано,
         * если пользовательский обработчик не был определён)*/
        if ($code & (E_RECOVERABLE_ERROR | E_USER_ERROR)) {
            exit;
        }
    }

    /**
     * Реализует механизм уведомления.
     *
     * Инстанцирует классы уведомителей, которые определены в конфигурационных файлах.<br>
     * Класс уведомителя должен расширять AbstractNotifier.<br>
     * Запускает на выполнение каждого уведомителя и, в случае ошибки,
     * отправляет текущий errorObject и саму ошибку во внутренний обработчик.<br>
     * Прекращает выполнение скрипта если уведомитель вернул true.
     *
     * @param ErrorObject  $errorObject  объект ошибки (wrapper)
     * @param ConfigObject $configObject объект конфигурации
     * @param ErrorHandler $errorHandler объект основного контроллера обработки ошибок
     */
    private function notify(ErrorObject $errorObject, ConfigObject $configObject, ErrorHandler $errorHandler)
    {
        $this->innerShutdownFatal = true;
        $exit = null;
        foreach ($configObject->getNotifiers() as $notifierClass => ${0}) {
            try {
                set_error_handler([$this, 'error']);

                if (!is_string($notifierClass)) {
                    throw new ErrorHandlerException(
                        'Notifiers name must be a string, '.gettype($notifierClass).' given'
                    );
                }
                $configObject->setNotifierClass($notifierClass);

                /* проверяем для конкретного Notifier надо ли обрабатывать ошибку */
                if (is_int($enabled = $configObject->get('enabled'))) {
                    if (0 === ($enabled & $errorObject->getCode())) {
                        continue;
                    }
                }

                /* проверяем игнорирует ли обработчик ошибки типа 'только для лога' */
                if ($errorObject->isLogType() && $configObject->get('ignoreLogType')) {
                    continue;
                }

                $notifier = new $notifierClass($errorObject, $configObject, $errorHandler);

                if (!$notifier instanceof AbstractNotifier) {
                    trigger_error(
                        $notifierClass.' must extend '.AbstractNotifier::class,
                        E_USER_ERROR
                    );
                    continue;
                }

                $exit = $notifier->run();

            } catch (\Throwable $e) {
                $this->exception($errorObject);
                $this->exception($e);
            } finally {
                restore_error_handler();
            }
        }
        $this->innerShutdownFatal = false;

        /* завершаем выполнение скрипта если уведомитель вернул true.
         * В частности используется в ServerErrorNotifier для возможности
         * прерывания скрипта при нефаталных ошибках*/
        if ($exit) exit;
    }

    /**
     * Возвращает значение флага внутренней фатальной ошибки.
     *
     * Если true - значит фатальная ошибка произошла внутри обработчика.
     *
     * @return bool
     */
    public function getInnerShutdownFatal(): bool
    {
        return $this->innerShutdownFatal;
    }

    /**
     * Обрабатывает внутренние ошибки.
     *
     * Конвертирует ошибку в исключение и передает в $this->exception().
     *
     * @param int    $code    сод ошибки
     * @param string $message сообщение ошибки
     * @param string $file    полное имя файла ошибки
     * @param int    $line    номер строки
     * @return bool true
     */
    public function error($code, $message, $file, $line)
    {
        $this->exception(new \ErrorException($message, $code, $code, $file, $line));
        return true;
    }

    /**
     * Обрабатывает внутренние исключения.
     *
     * Инстанцирует внутренний обработчик ошибок и передаёт ему ошибку.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     */
    public function exception($e)
    {
        $this->selfErrorHandler
            ?: $this->selfErrorHandler = new SelfErrorHandler($this->configObject);
        $this->selfErrorHandler->report($e);
    }
}
