<?php
declare(strict_types=1);

namespace MicroMir\Error\Trace;


class CliTraceHandler extends AbstractTraceHandler
{

    const MAGENTA = "\033[1;35m";
    const YELLOW  = "\033[0;33m";
    const GREEN   = "\033[1;30m";
    const CYAN    = "\033[0;36m";
    const GRAY    = "\033[3;37m";
    const RST     = "\033[0m";

    const TYPE    = self::GRAY;

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
        $arr['class'] = $class ?  $class."\n->" : "\n  ";
    }

    protected function functionName(string $function, array &$arr)
    {
        $arr['function'] = $function;
    }

    protected function objectArg($arg): string
    {
        return static::GREEN.$this->space('obj').static::RST.get_class($arg);
    }

    protected function arrayArg($arg): string
    {
        $preview = '';
        foreach ($arg as $key => ${0}) {
            $preview .= '['.$key.']=>..., ';
            if (mb_strlen($preview) > $this->stringLength) break;
        }
        return static::GREEN.$this->space('array['.count($arg).']').static::RST.$preview;
    }

    protected function stringArg($arg): string
    {
        $length = mb_strlen($arg);
        $str = static::GREEN.$this->space('['.$length.']str').static::RST
            .static::GRAY.substr($arg, 0, $this->stringLength);
        if ($length > $this->stringLength) $str .= '...';
        return $str.static::RST;
    }

    protected function numericArg($arg): string
    {
        return static::GREEN.$this->space('num').static::RST.$arg;
    }

    protected function boolArg($arg): string
    {
        $arg = $arg === true ? 'true' : 'false';
        return static::GREEN.$this->space('bool').static::RST.$arg;
    }

    protected function nullArg(): string
    {
        return static::GREEN.$this->space('null').static::RST;
    }

    protected function otherArg($arg): string
    {
        return static::GREEN.$this->space('other').static::RST.(string)$arg;
    }

    protected function completion(): string
    {
        $trace = '';
        $trace .= self::MAGENTA.'trace >>>'.static::RST."\n";
        foreach ($this->arr as $v) {
            $trace .= static::CYAN.$v['file'].'::'.$v['line'].static::RST." ".$v['class'];
            $trace .= static::YELLOW.$v['function'].'('.static::RST;

            foreach ($v['args'] as $arg) {
                $trace .= "\n".$arg;
            }
            $trace .= "\n".static::YELLOW.$this->space(')', 5).static::RST."\n";
        }
        return $trace .= self::MAGENTA.'<<< trace_end'.static::RST."\n";
    }

}