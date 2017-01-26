<?php

namespace MicroMir\Error\Trace;


class CliTraceHandler extends AbstractTraceHandler
{
    protected function fileName(int $i)
    {
        $this->arr[$i]['file'] = $this->dBTrace[$i]['file'];
    }

    protected function line(int $i)
    {
        $this->arr[$i]['line'] = $this->dBTrace[$i]['line'];
    }

    protected function className(int $i)
    {
        $this->arr[$i]['class'] = $this->dBTrace[$i]['class'];
    }

    protected function functionName(int $i)
    {
        $this->arr[$i]['function'] = $this->dBTrace[$i]['function'];
    }

    protected function objectArg(int $i, $arg)
    {
        $this->arr[$i]['args'][] = '{obj} '.get_class($arg);
    }

    protected function arrayArg(int $i, $arg)
    {
        $this->arr[$i]['args'][] = '{array}['.count($arg).']';
    }

    protected function stringArg(int $i, $arg)
    {
        $length = mb_strlen($arg);
        ${0} = '{str='.$length.'} '.substr($arg, 0, 20);
        if ($length > 20) ${0} .= ' ...';
        $this->arr[$i]['args'][] = ${0};
    }

    protected function numericArg(int $i, $arg)
    {
        $this->arr[$i]['args'][] = '{num} '.$arg;
    }

    protected function boolArg(int $i, $arg)
    {
        $arg === true ? ${0} = 'true' : ${0} = 'false';
        $this->arr[$i]['args'][] = '{bool} '.${0};
    }

    protected function nullArg(int $i)
    {
        $this->arr[$i]['args'][] = '{null} ';
    }

    protected function otherArg(int $i, $arg)
    {
        $this->arr[$i]['args'][] = (string)$arg;
    }

    protected function countArgs(int $i)
    {
        return;
    }

    protected function httpTable()
    {
        ${0} = '';
        foreach ($this->arr as $v) {

            if (empty($v['args'])) {
                echo("                    +\n");
            }

            foreach ($v['args'] as $argsValue) {
                echo('                    + '.$argsValue."\n");
            }

            echo('            {f} '.$v['function']."\n");
            echo('          ====> '.$v['class']."\n");
            echo($v['line'].' '
                .$v['file'].' '
                .$v['line']
                ."\n\n");
        }
    }

}