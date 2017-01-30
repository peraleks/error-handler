<?php
declare(strict_types=1);

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

    protected function before()
    {
        //TODO валидация настроек
    }

    protected function space(string $string, int $align = null)
    {
        return sprintf("%".($align ?? $this->align)."s", $string).' ';
    }

    protected function file(string $file, &$arr)
    {
        $arr['file'] = $file;
    }

    protected function line(int $line, array &$arr)
    {
        $arr['line'] = (string)$line;
    }

    protected function className(string $class, array &$arr)
    {
        $arr['class'] = $class;
    }

    protected function functionName(string $function, array &$arr)
    {
        $arr['function'] = $function;
    }

    protected function objectArg($arg): string
    {
        return static::GREEN.$this->space('obj').static::rst.get_class($arg);
    }

    protected function arrayArg($arg): string
    {
        $preview = '';
        foreach ($arg as $key => ${0}) {
            $preview .= '['.$key.']=>..., ';
            if (mb_strlen($preview) > $this->stringLength) break;
        }
        return static::GREEN.$this->space('array['.count($arg).']').static::rst.$preview;
    }

    protected function stringArg($arg): string
    {
        $length = mb_strlen($arg);
        $str = static::GREEN.$this->space('['.$length.']str').static::rst
            .static::GRAY.substr($arg, 0, $this->stringLength);
        if ($length > $this->stringLength) $str .= '...';
        return $str.static::rst;
    }

    protected function numericArg($arg): string
    {
        return static::GREEN.$this->space('num').static::rst.$arg;
    }

    protected function boolArg($arg): string
    {
        $arg = $arg === true ? 'true' : 'false';
        return static::GREEN.$this->space('bool').static::rst.$arg;
    }

    protected function nullArg(): string
    {
        return static::GREEN.$this->space('null').static::rst;
    }

    protected function otherArg($arg): string
    {
        return static::GREEN.$this->space('other').static::rst.(string)$arg;
    }

    protected function completion(): string
    {
        $trace = '';
        $trace .= self::MAGENTA.'trace >>>'.static::rst."\n";
        foreach ($this->arr as $v) {
            $trace .= static::CYAN.$v['file'].'::'.$v['line'].static::rst." ";
            $trace .= $v['class']."\n".' -> ';
            $trace .= static::YELLOW.$v['function'].'('.static::rst;

            foreach ($v['args'] as $arg) {
                $trace .= "\n".$arg;
            }
            $trace .= "\n".static::YELLOW.$this->space(')', 5).static::rst."\n";
        }
        return $trace .= self::MAGENTA.'<<< trace_end'.static::rst."\n";
    }

}