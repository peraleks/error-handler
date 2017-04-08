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

/**
 * Class ErrorObject
 *
 * Объект ошибки. Является обёрткой над объектом \Throwable
 * и полностью повторяет его интерфейс. По средствам дополнителных методов
 * предоставляет название ошибки из её кода, а так же название функции-обработчика
 * через которую ошибка пришла. Производит сопоставление всех исключений с кодом E_ERROR,
 * а ParseError c E_PARSE для более удобного управления при помощи битовой маски.
 */
class ErrorObject
{
    /**
     * Объект ошибки клиентской части скрипта.
     *
     * @var \Throwable
     */
    protected $e;

    /**
     * Код ошибки (severity)
     *
     * @var int
     */
    protected $code;

    /**
     * Тип ошибки полученный из $this->codeName для
     * стандартных ошибок и при помощи get_type() для исключений.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Любая строка, заданная пользователем.
     *
     * Будет трактоваться как тип ошибки в логах и т.д.
     * Является флагом для ServerErrorNotifier
     *
     * @var string
     */
    protected $logType;

    /**
     * Кеш массива стека вызовов с удалённым первым элементом.
     *
     * @var null | array
     */
    protected $trace;

    /**
     * Название функции обработчика ('error' | 'exception' | 'shutdown').
     *
     * @var string
     */
    protected $handler = '';

    /**
     * Соответствие кодов ошибок их названиям.
     *
     * @var array
     */
    protected  $codeName = [
        E_ERROR             => 'ERROR',
        E_WARNING           => 'WARNING',
        E_PARSE             => 'PARSE',
        E_NOTICE            => 'NOTICE',
        E_CORE_ERROR        => 'CORE_ERROR',
        E_CORE_WARNING      => 'CORE_WARNING',
        E_COMPILE_ERROR     => 'COMPILE_ERROR',
        E_COMPILE_WARNING   => 'COMPILE_WARNING',
        E_USER_ERROR        => 'USER_ERROR',
        E_USER_WARNING      => 'USER_WARNING',
        E_USER_NOTICE       => 'USER_NOTICE',
        E_STRICT            => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED        => 'DEPRECATED',
        E_USER_DEPRECATED   => 'USER_DEPRECATED',
    ];

    /**
     * ErrorObject constructor.
     *
     * Устанавливает код ошибки (из соображения универсальности
     * управления ошибками для исключения \ParseError - E_PARSE, для остальных
     * исключений - E_ERROR).<br>
     * Также определяет тип/название ошибки.
     *
     * @param \Throwable $e       объект ошибки клиентской части скрипта
     * @param string     $logType тип ошибки
     * @param string     $handler 'error' | 'exception' | 'shutdown' название функции обработчика
     */
    public function __construct(\Throwable $e, $logType = '', string $handler)
    {
        if (is_string($logType)) {
            $this->logType = $logType;
        }
        $this->handler = $handler;
        $this->e = $e;
        $this->code = $this->e->getCode();
        if ($this->e instanceof \ErrorException) {
            $this->type = $this->codeName[$this->code] ?? "unknown";
        } else {
            $this->code = $this->e instanceof \ParseError ? E_PARSE : E_ERROR;
            $this->type = get_class($this->e);
        }
    }

    /**
     * Возвращает тип (название) ошибки.
     *
     * @return string
     */
    public function getType(): string
    {
        if ('' !== $this->logType) return $this->logType;
        return $this->type;
    }

    /**
     * Если возвращает true, значит ошибка
     * только для лога. И тип ошибки задан пользователем
     * вручную.
     *
     * @return bool
     */
    public function isLogType(): bool
    {
        if ('' !== $this->logType) return true;
        return false;
    }

    /**
     * Возвращает название функции обработчика.
     *
     * @return string 'error' | 'exception' | 'shutdown'
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * Возвращает код ошибки (severity).
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Возвращает текст ошибки.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->e->getMessage();
    }

    /**
     * Возвращает полное имя файла, где произошла ошибка
     * с нормализованными слешами.
     *
     * @return string
     */
    public function getFile(): string
    {
        return str_replace('\\', '/', $this->e->getFile());
    }

    /**
     * Возврашает номер строки, где произошла ошибка.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->e->getLine();
    }

    /**
     * Возвращает массив со стеком вызовов.
     *
     * @return array
     */
    public function getTrace(): array
    {
        if ($this->trace) {
            return $this->trace;
        } elseif ($this->e instanceof \ErrorException) {
        /* для \ErrorException удаляем первый лишний элемент
         * и кешируем, чтобы не повторять операцию сдвига массива */
            $this->trace = $this->e->getTrace();
            array_shift($this->trace);
            return $this->trace;
        } else {
            return $this->e->getTrace();
        }
    }

    /**
     * Возвращает стек вызовов ввиде строки.
     *
     * @return string
     */
    public function getTraceAsString(): string
    {
        return $this->e->getTraceAsString();
    }

    /**
     * Возвращает предыдущую ошибку.
     *
     * @return \Throwable
     */
    public function getPrevious(): \Throwable
    {
        return $this->e->getPrevious();
    }

    /**
     * Возвращает полную информацию об ошибке
     * со стеком вызовов ввиде строки.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->e;
    }
}
