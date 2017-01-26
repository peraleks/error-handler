<?php

namespace MicroMir\Error\Trace;


class CliTraceHandler extends AbstractTraceHandler
{
    const MAGENTA = "\033[1;35m";
    const YELLOW  = "\033[0;33m";
    const GREEN   = "\033[0;32m";
    const CYAN    = "\033[0;36m";
    const GRAY    = "\033[0;37m";
    const rst     = "\033[0m";

    protected $align = 15;

    protected $stringLength = 80;

    protected function space(string $string, int $align = null)
    {
        $align = $align ?: $this->align;
        return sprintf("%".$align."s", $string).' ';
    }

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
        $this->arr[$i]['args'][] = static::GREEN.$this->space('obj').static::rst.get_class($arg);
    }

    protected function arrayArg(int $i, $arg)
    {
        ${0} = '';
        foreach ($arg as $key => $v) {
            ${0} .= '['.$key.']=>..., ';
            if (mb_strlen(${0}) > $this->stringLength) break;
        }
        $this->arr[$i]['args'][] = static::GREEN.$this->space('array['.count($arg).']').static::rst.${0};
    }

    protected function stringArg(int $i, $arg)
    {
        $length = mb_strlen($arg);
        ${0} = static::GREEN.$this->space('['.$length.']str').static::rst.substr($arg, 0, $this->stringLength);
        if ($length > $this->stringLength) ${0} .= ' ...';
        $this->arr[$i]['args'][] = ${0};
    }

    protected function numericArg(int $i, $arg)
    {
        $this->arr[$i]['args'][] = static::GREEN.$this->space('num').static::rst.$arg;
    }

    protected function boolArg(int $i, $arg)
    {
        $arg === true ? ${0} = 'true' : ${0} = 'false';
        $this->arr[$i]['args'][] = static::GREEN.$this->space('bool').static::rst.${0};
    }

    protected function nullArg(int $i)
    {
        $this->arr[$i]['args'][] = static::GREEN.$this->space('null').static::rst;
    }

    protected function otherArg(int $i, $arg)
    {
        $this->arr[$i]['args'][] = static::GREEN.$this->space('other').static::rst.(string)$arg;
    }

    protected function countArgs(int $i) { return; }

    protected function httpTable()
    {
        $this->traceResult .= self::MAGENTA.'trace >>>'.static::rst."\n";
        foreach ($this->arr as $v) {

            $this->traceResult .= static::CYAN.$v['file'].'::'.$v['line'].static::rst." ";

            $this->traceResult .= $v['class']."\n".' -> ';

            $this->traceResult .= static::YELLOW.$v['function'].'('.static::rst;

            foreach ($v['args'] as $arg) {
                $this->traceResult .= "\n".$arg;
            }
            $this->traceResult .= "\n".static::YELLOW.$this->space(')', 5).static::rst."\n";
        }
        $this->traceResult .= self::MAGENTA.'<<< trace_end'.static::rst."\n";
    }

}