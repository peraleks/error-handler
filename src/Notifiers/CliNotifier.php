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

use Peraleks\ErrorHandler\Core\ErrorObject;
use Peraleks\ErrorHandler\Core\ConfigInterface;
use Peraleks\ErrorHandler\Trace\CliSimpleTraceHandler;
use Peraleks\ErrorHandler\Trace\CliTraceHandler;

/**
 * Class CliNotifier
 *
 * Форматирует и выводит ошибку в CLI режиме.
 */
class CliNotifier extends AbstractNotifier
{
    const ERROR      = "\033[30;41m%s\033[0m";
    const WARNING    = "\033[31;43m%s\033[0m";
    const NOTICE     = "\033[1;30;43m%s\033[0m";
    const PARSE      = "\033[45m%s\033[0m";
    const DEPRECATED = "\033[30;47m%s\033[0m";
    const FILE       = "\033[0;36m%s\033[0m";
    const MESSAGE    = "\033[37m%s\033[0m";
    const TRACE      = "\033[1;35m%s\033[0m";

    /**
     * Соответствие кодов ошибок и цвета.
     *
     * @var array
     */
    protected $codeColor;

    /**
     * Инициализирует массив соответствия кодов ошибок и цвета.
     *
     * @return void
     */
    protected function prepare()
    {
        $this->codeColor = [
            E_ERROR             => static::ERROR,
            E_CORE_ERROR        => static::ERROR,
            E_COMPILE_ERROR     => static::ERROR,
            E_USER_ERROR        => static::ERROR,
            E_RECOVERABLE_ERROR => static::ERROR,

            E_WARNING         => static::WARNING,
            E_CORE_WARNING    => static::WARNING,
            E_COMPILE_WARNING => static::WARNING,
            E_USER_WARNING    => static::WARNING,

            E_PARSE => static::PARSE,

            E_NOTICE      => static::NOTICE,
            E_USER_NOTICE => static::NOTICE,

            E_STRICT          => static::DEPRECATED,
            E_DEPRECATED      => static::DEPRECATED,
            E_USER_DEPRECATED => static::DEPRECATED,
        ];
    }

    /**
     * Возвращает имя класса обработчика стека вызовов.
     *
     * @return string CliSimpleTraceHandler::class | CliTraceHandler::class
     */
    protected function getTraceHandlerClass(): string
    {
        return $this->configObject->get('simpleTrace')
            ? CliSimpleTraceHandler::class
            : CliTraceHandler::class;
    }

    /**
     * Форматирует ошибку для цветного вывода в терминале.
     *
     * @param string $trace  стек вызовов
     * @return string  ошибка в формате строки
     */
    protected function ErrorToString(string $trace): string
    {
        $eObj = $this->errorObject;

        $code    = $eObj->getCode();
        $eName   = $eObj->getType();
        $file    = $eObj->getFile();
        $line    = $eObj->getLine();
        $message = $eObj->getMessage();

        if ('' !== $trace) {
            $str = "\n".sprintf(static::TRACE, 'trace >>>')."\n";
            $appDir = $this->configObject->getAppDir();
            $fullFile = $eObj->getFile();
            $file = preg_replace('#^'.$appDir.'#', '', $fullFile);
            $str .= $fullFile === $file ? '' : sprintf(static::FILE, '('.$appDir.")\n");
            $str .= sprintf(static::MESSAGE, $trace);
            $trace = $str.sprintf(static::TRACE, '<<< trace_end');
        }

        return
            sprintf($this->codeColor[$code], "[$code] $eName ")
            .sprintf(static::FILE, " $file($line) ")."\n"
            .sprintf(static::MESSAGE, $message)
            .$trace;
    }

    /**
     * Выводит ошибку на стандартный вывод
     *
     * @return void
     */
    public function notify()
    {
        echo "\n".$this->finalStringError."\n";
    }
}
