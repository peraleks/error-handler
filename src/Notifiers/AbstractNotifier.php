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

namespace Peraleks\ErrorHandler\Notifiers;

use Peraleks\ErrorHandler\Core\ConfigObject;
use Peraleks\ErrorHandler\Core\ErrorHandler;
use Peraleks\ErrorHandler\Core\ErrorObject;

/**
 * Class AbstractNotifier
 *
 * Определяет шаблонный метод и интерфейс для уведомителей.
 * Все уведомители должны расширять данный класс.
 *
 * @package Peraleks\ErrorHandler
 */
abstract class AbstractNotifier
{
    /**
     * Объект ошибки (wrapper).
     *
     * @var ErrorObject
     */
    protected $errorObject;

    /**
     * Объект конфигурации.
     *
     * @var ConfigObject
     */
    protected $configObject;

    /**
     * Объект основного контроллера обработки ошибок.
     *
     * @var ErrorHandler
     */
    protected $errorHandler;

    /**
     * Окончателный результат обработки ошибки ввиде строки.
     *
     * @var string
     */
    protected $finalStringError;

    /**
     * AbstractNotifier constructor.
     *
     * Реализует шаблонный метод для уведомителей.
     *
     * @param ErrorObject $errorObject объект ошибки (wrapper)
     * @param ConfigObject $configObject объект конфигурации
     * @param ErrorHandler $errorHandler объект основного контроллера обработки ошибок
     */
    public function __construct(
        ErrorObject $errorObject,
        ConfigObject $configObject,
        ErrorHandler $errorHandler
    ) {
        $this->errorObject = $errorObject;
        $this->configObject = $configObject;
        $this->errorHandler = $errorHandler;
        $this->prepare();
        $this->finalStringError = $this->ErrorToString($this->TraceToString($this->getTraceHandlerClass()));
    }

    /**
     * Первый этап шаблонного метода.
     *
     * Здесь проводим валидацю параметров конфигурации и
     * устанавливаем значения по умолчанию.
     *
     * @return void
     */
    abstract protected function prepare();

    /**
     * Возвращает полное имя класса обработчика стека вызовов.
     *
     * Второй этап шаблонного метода.<br>
     * Если стек вызовов не требуется обрабатывать, просто верните
     * пустую строку.
     *
     * @return string полное имя класса обработчика стека вызовов
     */
    abstract protected function getTraceHandlerClass(): string;

    /**
     * Возвращает стек вызовов ввиде строки.
     *
     * Третий этап шаблонного метода.<br>
     * Получает стек вызовов при помощи обработчика,
     * имя которого было определено в getTraceHandlerClass().
     *
     * @param string $traceHandlerClass полное имя обработчика стека вызовов
     * @return string стек вызовов
     */
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

    /**
     * Возвращает окончателный результат обработки ошибки ввиде строки.
     *
     * Четвёртый этап шаблонного метода.<br>
     * Если стек вызовов не обрабатывался $trace будет равно пустой строке.<br>
     * Возвращаемый результат будет записан в $this->finalStringError.
     *
     * @param string $trace стек вызовов
     * @return string окончателный результат обработки ошибки
     */
    abstract protected function ErrorToString(string $trace): string;

    /**
     * Выполняет вывод подготовленной ошибки.
     *
     * Последний этап шаблонного метода.<br>
     * Подготовленная строка ошибки находится в $this->finalStringError
     * <br>
     * Если хотите прервать выполнение скрипта даже если
     * ошибка была не фатальной верните true.
     *
     * @return void | true
     */
    abstract public function notify();
}
