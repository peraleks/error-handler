<?php
declare(strict_types=1);

namespace MicroMir\Error\Trace;


class HtmlTraceHandler extends AbstractTraceHandler
{
    protected $stringLength = 80;

    protected $tooltipLength = 1500;

    protected $recLevel = 1;

    protected $recursion = 0;

    const tooltipEnable = 'tooltip_wrap';

    const FILE       = '<td class="trace_file">%s</td>';

    const PATH       = '<td class="trace_path">%s</td>';

    const LINE       = '<td class="trace_line">%s</td>';

    const CLASS_NAME = '<td class="trace_class">%s</td>';

    const N_SPACE    = '<td class="trace_name_space">%s</td>';

    const FUNC       = '<td class="trace_function">%s</td>';

    const PARAMS     = '<td class="trace_function_params">%s</td>';

    const ARGS       = '<td class="trace_args">%s</td>';

    const NUM        = '<td class="trace_args numeric">%s</td>';

    const CALL       = '<td class="trace_args callable">%s</td>';

    const STRING     = '<td class="trace_args string tooltip"><span>%s&prime;</span>%s<span>&prime;</span><div class="%s hidden string"><span>&prime;</span>%s<span>&prime;</span></div></td>';

    const ARR        = '<td class="trace_args array tooltip">%s<div class="tooltip_wrap hidden">%s</div></td>';

    const RESOURCE   = '<td class="trace_args resource tooltip">%s<div class="tooltip_wrap hidden">%s</div></td>';

    const BOOL       = '<td class="trace_args bool">%s</td>';

    const ETC        = '<span class="etc">...</span>';

    const S_CLASS_NAME = '<span class="trace_class">%s</span>';

    const S_N_SPACE    = '<span class="trace_name_space">%s</span>';

    const TABLE        = '<table>%s</table>';

    const EMPTY_ARGS   = '<td class="trace_args empty"></td>';

    const TR           = '<tr>%s</tr>';

    const TD           = '<td>%s</td>';

    const QUOTES       = '<span class="string_quotes">&prime;</span>';

    protected function before()
    {
        //TODO валидацю массива настроек
        !is_int(${0} = $this->settings->get('stringLength'))
            ?: $this->stringLength = ${0};
    }

    protected function file(string $file): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $file);
        //получаем имя файла без пути
        $file = sprintf(static::FILE, '/'.array_pop($parts));
        //получаем путь (уже без имени файла) относительно корня приложения для экономии пространства в таблице
        $path = preg_replace('#^'.$this->settings->appDir().'#', '', implode('/', $parts));
        $path = sprintf(static::PATH, $path);

        return $path.$file;
    }

    protected function line(int $line): string
    {
        return sprintf(static::LINE, $line);
    }

    protected function className(string $class): string
    {
        //получаем имя класса без пространства имён
        $parts = explode('\\', $class);
        $class = sprintf(static::CLASS_NAME, array_pop($parts));
        //получаем пространство имён без имени класса
        $parts[] = '';
        $nameSpace = sprintf(static::N_SPACE, implode('\\', $parts));

        return $nameSpace.$class;
    }

    protected function functionName(string $func, string $class, int $cntArgs): string
    {
        if ('' != $class) {
            $ref = new \ReflectionMethod($class, $func);
        } elseif (function_exists($func)) {
            $ref = new \ReflectionFunction($func);
        }
        $p = sprintf(static::TD, '');
        if (isset($ref)) {
            $param = $ref->getNumberOfParameters();
            $reqParam = $ref->getNumberOfRequiredParameters();
            $c = $reqParam > $cntArgs ? ' unset '.($reqParam - $cntArgs) : '';
            $p = sprintf(static::PARAMS, $param.'.'.$reqParam.$c);
        }
        return sprintf(static::FUNC, $func).$p;
    }

    protected function stringArg($arg): string
    {
        $length = mb_strlen($arg);
        $string = mb_substr($arg, 0, $this->stringLength);
        $string = preg_replace('/\s/', '&nbsp;', $string);

        if ($length > $this->stringLength) {
            // просмотр полной строки, но не длиннее tooltipLength
            $tooltip = mb_substr($arg, 0, $this->tooltipLength);
            $tooltip = htmlentities($tooltip, ENT_SUBSTITUTE | ENT_COMPAT);
            $tooltip = preg_replace('/\s/', '&nbsp;', $tooltip);
            $end = static::ETC;
            $css_class = static::tooltipEnable;
        } else {
            $tooltip = $end = '';
            $css_class = '';
        }
        return sprintf(static::STRING, $length, $string.$end, $css_class, $tooltip);
    }

    protected function numericArg($arg): string
    {
        return sprintf(static::NUM, $arg);
    }

    protected function arrayArg($arg): string
    {
        if ($this->recursion > $this->recLevel) { return sprintf(static::TD, static::ETC); }
        ++$this->recursion;
        $tooltip = $this->arrayHandler($arg);
        --$this->recursion;
        return sprintf(static::ARR, 'array['.count($arg).']', $tooltip);
    }

    protected function arrayHandler(array $array): string
    {
        $tr = '';
        foreach ($array as $key => $value) {
            $key = htmlentities((string)$key, ENT_SUBSTITUTE | ENT_COMPAT);
            $key = preg_replace('/\s/', '&nbsp;', $key);
            $tr .= sprintf(static::TD, $key);
                if (is_string($value))  $tr .= $this->stringArg($value);
            elseif (is_numeric($value)) $tr .= $this->numericArg($value);
            elseif (is_array($value))   $tr .= $this->arrayArg($value);
            elseif (is_bool($value))    $tr .= $this->boolArg($value);
            elseif (is_null($value))    $tr .= $this->nullArg();
            elseif ($value instanceof \Closure)$tr .= $this->callableArg($value);
            elseif (is_object($value))  $tr .= $this->objectArg($value);
            elseif (is_resource($value))$tr .= $this->resourceArg($value);
            else $tr .= sprintf(static::TD, gettype($value));
            $tr = sprintf(static::TR, $tr);
        }
        return sprintf(static::TABLE, $tr);

    }

    protected function boolArg($arg): string
    {
        return sprintf(static::BOOL, $arg === true ? 'true' : 'false');
    }

    protected function nullArg(): string
    {
        return sprintf(static::BOOL, 'null');
    }

    protected function callableArg($arg): string
    {
        return sprintf(static::CALL, 'callable');
    }

    protected function objectArg($arg): string
    {
        $parts = explode('\\', get_class($arg));

        //имя класса без пространства имён
        $obj = sprintf(static::S_CLASS_NAME, array_pop($parts));

        //пространство имён без имени класса
        $space = sprintf(static::S_N_SPACE, implode('\\', $parts).'\\');

        return sprintf(static::ARGS, $space.$obj);
    }

    protected function resourceArg($arg): string
    {
        return sprintf(static::RESOURCE, 'resource', $this->arrayHandler(stream_get_meta_data($arg)));
    }

    protected function otherArg($arg): string
    {
        // определяем является ли тип закрытым ресурсом
        if ('unknown type' === $type = gettype($arg)) {
            ob_start();
            echo $arg;
            $string = ob_get_clean();
            if (preg_match('/^Resource id (\#\d+)$/', $string, $arr)) $type = 'closed resource '.$arr[1];
        }

        return sprintf( static::RESOURCE, $type, '');
    }


    protected function completion(): string
    {
        $trace = '';
        foreach ($this->arr as $v) {
            $tr = $v['file'].$v['line'].$v['class'].$v['function'];

            isset($v['args']) ?: $v['args'] = [];

            for ($k = 0; $k < $this->maxNumberOfArgs; ++$k) {
                $tr .= $v['args'][$k] ?? static::EMPTY_ARGS;
            }
            $trace .= sprintf(static::TR, $tr);
        }
        return sprintf(static::TABLE, $trace);
    }

}