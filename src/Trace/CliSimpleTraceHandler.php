<?php

namespace Peraleks\ErrorHandler\Trace;

class CliSimpleTraceHandler extends CliTraceHandler
{
    protected function className(string $class, string $type): string
    {
        return sprintf(static::CLASS_NAME, $class.' '.sprintf(static::TYPE, $type).' ');
    }

    protected function functionName(string $func, string $param): string
    {
        $param === '' ?: $param = '{'.$param.'}';

        return sprintf(static::FUNC, $func.$param."\n");
    }

    protected function completion(): string
    {
        $trace = '';
        $trCount = 0;
        foreach ($this->arr as $v) {
            $trace .= sprintf(static::TRACE_CNT, '#'.$trCount).$v['file'].$v['line']." ".$v['class'];
            $trace .= $v['function'];
            ++$trCount;
        }
        return $trace;
    }
}
