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

class HtmlTraceFormatter extends AbstractTraceFormatter
{
    const TOOLTIP_ENABLE = 'tooltip_wrap';

    const FILE       = '<td class="trace_file">%s</td>';

    const PATH       = '<td class="trace_path">%s</td>';

    const LINE       = '<td class="trace_line">%s</td>';

    const CLASS_NAME = '<td class="trace_class">%s</td>';

    const CALL_TYPE  = '<td class="trace_call_type">%s</td>';

    const N_SPACE    = '<td class="trace_name_space">%s</td>';

    const FUNC       = '<td class="trace_function">%s</td>';

    const PARAMS     = '<td class="trace_function_params">%s</td>';

    const DOC        = '<span class="doc">*</span><div class="doc_wrap hidden"><div class="doc_window">'
                       .'<div class="doc_data">%s</div><div class="doc_text">%s</div></div></div>';

    const ARGS       = '<td class="trace_args">%s</td>';

    const ARGS_DOC   = '<td class="trace_args">%s<div class="doc hidden">%s</div></td>';

    const NUM        = '<td class="trace_args numeric">%s</td>';

    const CALL       = '<td class="trace_args callable">%s<div class="tooltip_wrap hidden">%s</div></td>';

    const STRING     = '<td class="trace_args string tooltip"><span>%s&prime;</span>%s<span>&prime;</span>'
                        .'<div class="%s hidden string"><span>&prime;</span>%s<span>&prime;</span></div></td>';

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

    protected $stringLength = 80;

    protected $tooltipLength = 1000;

    protected $arrayLevel = 2;

    protected $recursion = 0;

    protected function before()
    {
        !is_int($level  = $this->configObject->get('arrayLevel')) ?: $this->arrayLevel = $level;
        !is_int($length = $this->configObject->get('stringLength')) ?: $this->stringLength = $length;
        !is_int($length = $this->configObject->get('tooltipLength')) ?: $this->tooltipLength = $length;
    }

    protected function file(string $file): string
    {
        if ('' === $file) return sprintf(static::PATH, $file).sprintf(static::FILE, '');
        $parts = explode(DIRECTORY_SEPARATOR, $file);

        //получаем имя файла без пути
        $file = sprintf(static::FILE, '/'.array_pop($parts));

        //получаем путь (уже без имени файла) относительно корня приложения для экономии пространства в таблице
        $path = preg_replace('#^'.$this->configObject->getAppDir().'#', '', implode('/', $parts));
        $path = sprintf(static::PATH, $path);

        return $path.$file;
    }

    protected function line(int $line): string
    {
        $line !== 0 ?: $line = '';
        return sprintf(static::LINE, $line);
    }

    protected function className(string $class, string $type): string
    {
        $parts = explode('\\', $class);

        //получаем имя класса без пространства имён
        $className = array_pop($parts);

        if ('' !== $class) {
            $r = new \ReflectionClass($class);
            if ($doc = $r->getDocComment()) {
                $doc = $this->formatDocToHtml($doc);
                $name = $r->getName();
                !$doc ?: $className .= sprintf(static::DOC, $name, $doc);
            }
        }
        $class = sprintf(static::CLASS_NAME, $className);

        //получаем пространство имён без имени класса
        $parts[] = '';
        $nameSpace = sprintf(static::N_SPACE, implode('\\', $parts));

        //тип вызова функции
        $type = sprintf(static::CALL_TYPE, $type);

        return $nameSpace.$class.$type;
    }

    protected function formatDocToHtml(string $doc): string
    {
        $doc = preg_replace('/\n\s*\*/', "\n *", $doc);
        $doc = htmlentities($doc, ENT_SUBSTITUTE | ENT_COMPAT);
        $doc = preg_replace('/ /', '&nbsp;', $doc);
        return str_replace("\n", '<br>', $doc);
    }

    protected function functionName(string $func, string $param, string $doc): string
    {
       if ('' !== $doc) {
           $func .= sprintf(static::DOC, $func, $this->formatDocToHtml($doc));
       }
        return sprintf(static::FUNC, $func).sprintf(static::PARAMS, $param);
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
            
            if ($length > $this->tooltipLength) {
                $tooltip .= static::ETC;
            }
            $end = static::ETC;
            $css_class = static::TOOLTIP_ENABLE;
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
        if ($this->recursion > $this->arrayLevel) {
            return sprintf(static::ARGS, static::ETC);
        }
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

            /* останавливаем рекурсию $GLOBALS */
            if ($value === $GLOBALS) {
                $tr .= sprintf(static::ARGS, static::ETC);
                $tr = sprintf(static::TR, $tr);
                continue;
            }
            if (is_string($value))       $tr .= $this->stringArg($value);
            elseif (is_numeric($value))  $tr .= $this->numericArg($value);
            elseif (is_array($value))    $tr .= $this->arrayArg($value);
            elseif (is_bool($value))     $tr .= $this->boolArg($value);
            elseif (is_null($value))     $tr .= $this->nullArg();
            elseif ($value instanceof \Closure) $tr .= $this->callableArg($value);
            elseif (is_object($value))   $tr .= $this->objectArg($value);
            elseif (is_resource($value)) $tr .= $this->resourceArg($value);
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

    protected function objectArg($arg): string
    {
        $class = get_class($arg);
        $parts = explode('\\', $class);

        //получаем имя класса без пространства имён
        $className = array_pop($parts);

        if ('' !== $class) {
            $r = new \ReflectionClass($class);
            $doc = str_replace("\n", '<br>', $r->getDocComment());
            $file = $r->getFileName();
            $name = $r->getName();
            !$doc ?: $className .= sprintf(static::DOC, $name, $doc);
        }

        //имя класса без пространства имён
        $obj = sprintf(static::S_CLASS_NAME, $className);

        //пространство имён без имени класса
        $space = sprintf(static::S_N_SPACE, implode('\\', $parts).'\\');

        return sprintf(static::ARGS, $space.$obj);
    }

    protected function callableArg($arg): string
    {
        $r = new \ReflectionFunction($arg);
        $arr = [];
        $start = $r->getStartLine();
        $end = $r->getEndLine();
        $fileName = $r->getFileName();
        $arr['code'] = array_slice(file($fileName), $start - 1, $end - $start + 1, true);
        $arr['this'] = $r->getClosureThis();
        $arr['file name'] = $r->getFileName();

        return sprintf(static::CALL, $r->getName(), $this->arrayHandler($arr));
    }

    protected function resourceArg($arg): string
    {
        $res = 'resource';
        ob_start();
        echo $arg;
        if (preg_match('/^Resource id (\#\d+)$/', ob_get_clean(), $arr)) {
            $res .= $arr[1];
        }
        return sprintf(static::RESOURCE, $res , $this->arrayHandler(stream_get_meta_data($arg)));
    }

    protected function closedResourceArg(string $string): string
    {
        return sprintf(static::RESOURCE, $string, '');
    }

    protected function otherArg($arg): string
    {
        return sprintf(static::ARGS, gettype($arg));
    }

    protected function completion(array $traceArray): string
    {
        $trace = '';
        foreach ($traceArray as $v) {
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
