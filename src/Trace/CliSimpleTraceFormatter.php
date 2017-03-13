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

class CliSimpleTraceFormatter extends CliTraceFormatter
{
    protected function className(string $class, string $type): string
    {
        if ('' === $class.$type) return '';
        return sprintf(static::CLASS_NAME, $class.' '.sprintf(static::TYPE, $type).' ');
    }

    protected function functionName(string $func, string $param, string $doc): string
    {
        $param === '' ?: $param = '['.$param.']';

        return sprintf(static::FUNC, $func.$param."\n");
    }

    protected function completion(array $traceArray): string
    {
        $trace = '';
        for ($i = 0, $c = count($traceArray); $i < $c; ++$i) {
            $v =& $traceArray[$i];
            $trace .= sprintf(static::TRACE_CNT, '#'.$i).$v['file'].$v['line'].$v['class'].$v['function'];
        }
        return $trace;
    }
}
