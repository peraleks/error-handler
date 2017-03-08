<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Trace;


class FileTraceHandler extends AbstractTraceHandler
{
    protected function before() {}

    protected function completion(): string
    {
        $path = $this->configObject->getAppDir();
        $trace = '';
        $trCount = 0;
        $cnt = count($this->arr) - 2;
        foreach ($this->arr as $v) {
            $file = preg_replace('#^'.$path.'#', '', $v['file']);
            $trace .= '#'.$trCount.' '.$file.'('.$v['line'].'): '.$v['class'].' '.$v['function'];
            $cnt < $trCount ?: $trace .= "\n";
            ++$trCount;
        }
        return $trace;
    }
}