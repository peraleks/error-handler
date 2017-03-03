<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Trace;


class FileTraceHandler extends AbstractTraceHandler
{
    protected function before() {}

    protected function completion(): string
    {
        $trace = '';
        $trCount = count($this->arr);
        foreach ($this->arr as $v) {
            $trace .= '#'.--$trCount.' '.$v['file'].'('.$v['line'].'): '.$v['class'].' '.$v['function'];
            0 == $trCount ?: $trace .= "\n";
        }
        return $trace;
    }
}