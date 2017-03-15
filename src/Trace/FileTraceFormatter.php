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

namespace Peraleks\ErrorHandler\Trace;

/**
 * Class FileTraceFormatter
 *
 * Форматирует стек вызовов для записи в файл.
 */
class FileTraceFormatter extends AbstractTraceFormatter
{
    /**
     * Ничего не делает.
     */
    protected function before() {}

    /**
     * Здесь производим окончательное форматирование массива стека вызовов
     * и формируем конечную строку.
     *
     * @param array $traceArray предварительно отформатированный стек
     * @return string окончательный результат обработки стека
     */
    protected function completion(array $traceArray): string
    {
        $path = $this->configObject->getAppDir();
        $trace = '';
        for ($i = 0, $c = count($traceArray); $i < $c; ++$i) {
            $v =& $traceArray[$i];

            $file = preg_replace('#^'.$path.'#', '', $v['file']);

            $trace .= '#'.$i.' '.$file
                .('0' === $v['line'] ? '[internal function]: ' : '('.$v['line'].'): ')
                .$v['class'].$v['function'];

            $i > ($c - 2)  ?: $trace .= "\n";
        }
        return $trace;
    }
}