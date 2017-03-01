<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Trace;


class BrowserConsoleTraceHandler extends AbstractTraceHandler
{
    protected function before() {}

    protected function completion(): string
    {
        $trace = '';
        $trCount = count($this->arr);
        foreach ($this->arr as $v) {
            $trace .= '#'.--$trCount.' '.$v['file'].' ( '.$v['line'].' ) '.$v['class'].' '.$v['function'].'\n';
        }
        return $trace;
    }
}