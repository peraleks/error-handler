<?php
/**
 * PHP error handler and debugger.
 *
 * @package   Peraleks\ErrorHandler
 * @copyright 2017 Aleksey Perevoshchikov <aleksey.perevoshchikov.n@gmail.com>
 * @license   https://github.com/peraleks/error-handler/blob/master/LICENSE.md MIT
 * @link      https://github.com/peraleks/error-handler
 */

declare(strict_types=1);

namespace Peraleks\ErrorHandler\Trace;

class CliTraceFormatter extends AbstractTraceFormatter
{
    const FILE       = "\033[0;36m%s\033[0m";
    const LINE       = "\033[0;36m%s\033[0m";
    const CLASS_NAME = "\033[37m%s\033[0m";
    const FUNC       = "\033[33m%s\033[0m";
    const TYPE       = "\033[1;30m%s\033[0m";
    const OBJ        = "\033[1;30m%s\033[0m";
    const CALL       = "\033[0;34m%s\033[0m";
    const ARR        = "\033[35m%s\033[0m";
    const STRING     = "\033[32m%s\033[0m";
    const TRIM       = "\033[1;30m%s\033[0m";
    const NUM        = "\033[1;34m%s\033[0m";
    const BOOL       = "\033[31m%s\033[0m";
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
        return sprintf(static::FILE, preg_replace('#^'.$this->configObject->getAppDir().'#', '', $file));
    }

    protected function line(int $line): string
    {
        $line = 0 === $line ? '[internal function] ' : '('.(string)$line.') ';
        return sprintf(static::LINE, $line);
    }

    protected function className(string $class, string $type): string
    {
        '' !== $type ?: $type = '  ';
        return sprintf(static::CLASS_NAME, $class."\n".sprintf(static::TYPE, $type));
    }

    protected function functionName(string $func, string $param, string $doc): string
    {
        $param === '' ?: $param = '{'.$param.'}';

        return sprintf(static::FUNC, $func.$param.'(');
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
        $r = new \ReflectionFunction($arg);
        $start = $r->getStartLine();
        $fileName = $r->getFileName();
        $code = implode(array_slice(file($fileName), $start - 1, 1));
        $code = str_replace("\n", '', $code);
        $thisCl = $r->getClosureThis();
        if (is_object($thisCl)) {
            $thisCl = get_class($thisCl);
        }
        return sprintf(static::TYPE, $this->space('callable'))
            .sprintf(static::CALL, 'this: '.$thisCl)
            .sprintf(static::CALL, ' '.$fileName.'('.$start.')')
            .sprintf(static::CALL, mb_substr("\n".$code, 0, $this->stringLength + 15));
    }

    protected function resourceArg($arg): string
    {
        $res = 'resource';
        ob_start();
        echo $arg;
        if (preg_match('/^Resource id (\#\d+)$/', ob_get_clean(), $arr)) {
            $res .= $arr[1];
        }
        $s = stream_get_meta_data($arg);
        $mData = 'wr_type: '.$s['wrapper_type'].', mode: '.$s['mode'].', uri: '.$s['uri'];
        return sprintf(static::TYPE, $this->space($res))
            .sprintf(static::TYPE, $mData);
    }

    protected function closedResourceArg(string $string): string
    {
        return sprintf(static::TYPE, $this->space($string));
    }

    protected function otherArg($arg): string
    {
        return sprintf(static::TYPE, $this->space($this->isClosedResource($arg)));
    }

    protected function completion(array $traceArray): string
    {
        $trace = '';
        for ($i = 0, $c = count($traceArray); $i < $c; ++$i) {
            $v =& $traceArray[$i];
            $trace .= sprintf(static::TRACE_CNT, '#'.$i).$v['file'].$v['line'].$v['class'].$v['function'];

            foreach ($v['args'] as $arg) {
                $trace .= "\n".$arg;
            }
            $trace .= "\n".sprintf(static::FUNC, $this->space(')', 3))."\n";
        }
        return $trace;
    }
}
