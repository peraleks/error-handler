<?php
/**
 * PHP error handler and debugger.
 *
 * @package   Peraleks\ErrorHandler
 * @copyright 2017 Aleksey Perevoshchikov <aleksey.perevoshchikov.n@gmail.com>
 * @license   https://github.com/peraleks/error-handler/blob/master/LICENSE.md MIT
 * @link      https://github.com/peraleks/error-handler
 */

namespace Peraleks\ErrorHandler\Trace;

/**
 * Class CliSimpleTraceFormatter
 *
 * Форматирует стек вызовов для отображения в CLI режиме.
 * Упрощённый вариант CliTraceFormatter, не содержит подробной
 * информации о параметрах функций, только их количество.
 */
class CliSimpleTraceFormatter extends CliTraceFormatter
{

    /**
     * Здесь производим окончательное форматирование массива стека вызовов
     * и формируем конечную строку.
     *
     * @param array $traceArray предварительно отформатированный стек
     * @return string окончательный результат обработки стека
     */
    protected function completion(array $traceArray): string
    {
        $trace = '';
        for ($i = 0, $c = count($traceArray); $i < $c; ++$i) {
            $v =& $traceArray[$i];
            $trace .= sprintf(static::TRACE_CNT, '#'.$i).$v['file'].$v['line'].$v['class'].$v['function'];
        }
        return $trace;
    }

    /**
     * Возвращает форматированное имя класса и тип вызова метода.
     *
     * @param string $class имя класса
     * @param string $type  тип вызова метода (:: | ->)
     *
     * @return string
     */
    protected function className(string $class, string $type): string
    {
        if ('' === $class.$type) return '';
        return sprintf(static::CLASS_NAME, $class.' '.sprintf(static::TYPE, $type).' ');
    }

    /**
     * Возвращает форматированное имя функции и количество аргументов.
     *
     * @param string $function имя метода или функции
     * @param string $param    пустая строка или строка вида 'a.b'
     *                         где a - количество аргументов функции,
     *                         b - количество обязателных аргументов
     * @param string $doc      PHPDoc
     * @return string
     */
    protected function functionName(string $function, string $param, string $doc): string
    {
        $param === '' ?: $param = '['.$param.']';

        return sprintf(static::FUNC, $function.$param."\n");
    }
}
