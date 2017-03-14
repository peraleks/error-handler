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
use Peraleks\ErrorHandler\Exception\ErrorHandlerException;
use Peraleks\ErrorHandler\Trace\FormatterInterface;

/**
 * Class AbstractNotifier
 *
 * Определяет шаблонный метод и интерфейс для уведомителей.
 * Все уведомители должны расширять данный класс.
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
     * AbstractNotifier constructor.
     *
     * Реализует шаблонный метод для уведомителей.
     *
     * @param ErrorObject $errorObject объект ошибки (wrapper)
     * @param ConfigObject $configObject объект конфигурации
     * @param ErrorHandler $errorHandler объект основного контроллера обработки ошибок
     */
    final public function __construct(
        ErrorObject $errorObject,
        ConfigObject $configObject,
        ErrorHandler $errorHandler
    ) {
        $this->errorObject = $errorObject;
        $this->configObject = $configObject;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Реализует и запускает шаблонный метод для уведомителей.
     *
     * @return null | true флаг прерывания скрипта
     */
    final public function run()
    {
        $this->before();
        return $this->notify(
            $this->ErrorToString(
                $this->TraceToString(
                    $this->traceFormatterClass()
                )
            )
        );
    }

    /**
     * Первый этап шаблонного метода.
     *
     * Здесь проводим валидацю параметров конфигурации и
     * устанавливаем значения по умолчанию.
     *
     * @return void
     */
    abstract protected function before();

    /**
     * Возвращает полное имя класса обработчика стека вызовов.
     *
     * Второй этап шаблонного метода.<br>
     * Если стек вызовов не требуется обрабатывать, просто верните
     * пустую строку.
     *
     * @return string полное имя класса обработчика стека вызовов
     */
    abstract protected function traceFormatterClass(): string;

    /**
     * Возвращает стек вызовов ввиде строки.
     *
     * Третий этап шаблонного метода.<br>
     * Получает стек вызовов при помощи обработчика,
     * имя которого было определено в getTraceHandlerClass().
     *
     * @param string $traceFormatterClass полное имя обработчика стека вызовов
     * @return string стек вызовов
     * @throws ErrorHandlerException
     */
    protected function TraceToString(string $traceFormatterClass): string
    {
        $err = $this->errorObject;
        $con = $this->configObject;

        if ('' === $traceFormatterClass) return '';

        if (0 !== ($con->get('handleTrace') & $err->getCode())) {

            if ($con->get('phpNativeTrace')) return $err->getTraceAsString();

            $formatter = new $traceFormatterClass;

            if (!$formatter instanceof FormatterInterface) {
                throw new ErrorHandlerException(
                    $traceFormatterClass.' must implement '.FormatterInterface::class
                );
            }

            return  $formatter->getFormattedTrace($err->getTrace(), $con);
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
     * Если хотите прервать выполнение скрипта даже если
     * ошибка была не фатальной верните true.
     *
     * @param string $error форматированная ошибка
     * @return void | true флаг прерывания скрипта
     */
    abstract protected function notify(string $error);
}
