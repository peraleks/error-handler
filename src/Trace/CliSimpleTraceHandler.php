<?php

namespace Peraleks\ErrorHandler\Trace;

class CliSimpleTraceHandler extends CliTraceHandler
{
    protected function className(string $class, string $type): string
    {
        return sprintf(static::CLASS_NAME, $class.' '.sprintf(static::TYPE, $type).' ');
    }

    protected function functionName(string $func, string $params): string
    {
        $params === '' ?: $params = '{'.$params.'}';

        return sprintf(static::FUNC, $func.$params."\n");
    }

    protected function objectArg($arg): string
    {
        return '';
    }

    protected function arrayArg($arg): string
    {
        return '';
    }

    protected function stringArg($arg): string
    {
        return '';
    }

    protected function numericArg($arg): string
    {
        return '';
    }

    protected function boolArg($arg): string
    {
        return '';
    }

    protected function nullArg(): string
    {
        return '';
    }

    protected function callableArg($arg): string
    {
        return '';
    }

    protected function resourceArg($arg): string
    {
        return '';
    }

    protected function otherArg($arg): string
    {
        return '';
    }

    protected function completion(): string
    {
        $trace = '';
        $trace .= sprintf(self::TRACE, 'trace >>>')."\n";
        $trCount = count($this->arr);
        foreach ($this->arr as $v) {
            $trace .= sprintf(static::TRACE_CNT, '#'.--$trCount).$v['file'].$v['line']." ".$v['class'];
            $trace .= $v['function'];
        }
        return $trace .= sprintf(self::TRACE, '<<< trace_end')."\n";
    }
}
