<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Trace;

class CliTraceHandler extends AbstractTraceHandler
{
    const FILE       = "\033[0;36m%s\033[0m";
    const LINE       = "\033[0;36m%s\033[0m";
    const CLASS_NAME = "\033[37m%s\033[0m";
    const FUNC       = "\033[33m%s\033[0m";
    const TYPE       = "\033[1;30m%s\033[0m";
    const OBJ        = "\033[1;30m%s\033[0m";
    const ARR        = "\033[35m%s\033[0m";
    const STRING     = "\033[32m%s\033[0m";
    const TRIM       = "\033[1;30m%s\033[0m";
    const NUM        = "\033[1;34m%s\033[0m";
    const BOOL       = "\033[31m%s\033[0m";
    const TRACE      = "\033[1;35m%s\033[0m";
    const TRACE_CNT  = "\033[1;30m%s\033[0m";

    protected $align = 15;

    protected $stringLength = 80;

    protected function before()
    {
        !is_int($length = $this->configObject->get('stringLength')) ?: $this->stringLength = $length;
    }

    protected function space(string $string, int $align = null)
    {
        return sprintf("%".($align ?? $this->align)."s", $string).' ';
    }

    protected function file(string $file): string
    {
        return sprintf(static::FILE, $file);
    }

    protected function line(int $line): string
    {
        return sprintf(static::LINE, '('.(string)$line.')');
    }

    protected function className(string $class, string $type): string
    {
        return sprintf(static::CLASS_NAME, $class."\n".$type);
    }

    protected function functionName(string $func, string $params): string
    {
        $params === '' ?: $params = '{'.$params.'}';

        return sprintf(static::FUNC, $func.$params.'(');
    }

    protected function objectArg($arg): string
    {
        return sprintf(static::TYPE, ($this->space('obj'))).sprintf(static::OBJ, get_class($arg));
    }

    protected function arrayArg($arg): string
    {
        $preview = '';
        foreach ($arg as $key => ${0}) {
            $preview .= '['.$key.']=>..., ';
            if (mb_strlen($preview) > $this->stringLength) {
                break;
            }
        }
        return sprintf(static::TYPE, $this->space('array['.count($arg).']')).sprintf(static::ARR, $preview);
    }

    protected function stringArg($arg): string
    {
        $length = mb_strlen($arg);
        $type = sprintf(static::TYPE, $this->space('['.$length.']str'));
        $str = sprintf(static::STRING, mb_substr($arg, 0, $this->stringLength));
        if ($length > $this->stringLength) {
            $str .= sprintf(static::TRIM, '...');
        }
        return $type.$str;
    }

    protected function numericArg($arg): string
    {
        return sprintf(static::TYPE, $this->space('num')).sprintf(static::NUM, $arg);
    }

    protected function boolArg($arg): string
    {
        $arg = $arg === true ? 'true' : 'false';
        return sprintf(static::TYPE, $this->space('bool')).sprintf(static::BOOL, $arg);
    }

    protected function nullArg(): string
    {
        return sprintf(static::TYPE, $this->space('null'));
    }

    protected function callableArg($arg): string
    {
        return sprintf(static::TYPE, $this->space('callable'));
    }

    protected function resourceArg($arg): string
    {
        return sprintf(static::TYPE, $this->space('resource'));
    }

    protected function otherArg($arg): string
    {
        return sprintf(static::TYPE, $this->space($this->isClosedResource($arg)));
    }

    protected function completion(): string
    {
        $trace = '';
        $trace .= sprintf(self::TRACE, 'trace >>>')."\n";
        $trCount = count($this->arr);
        foreach ($this->arr as $v) {
            $trace .= sprintf(static::TRACE_CNT, '#'.--$trCount).$v['file'].$v['line']." ".$v['class'];
            $trace .= $v['function'];

            foreach ($v['args'] as $arg) {
                $trace .= "\n".$arg;
            }
            $trace .= "\n".sprintf(static::FUNC, $this->space(')', 3))."\n";
        }
        return $trace .= sprintf(self::TRACE, '<<< trace_end');
    }
}
