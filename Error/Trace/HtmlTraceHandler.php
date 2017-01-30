<?php
declare(strict_types=1);

namespace MicroMir\Error\Trace;


class HtmlTraceHandler extends AbstractTraceHandler
{
    protected $stringLength = 80;

    protected $titleLength = 1500;

    const TD         = '</td>';
    const FILE       = '<td class="trace_file">';
    const PATH       = '<td class="trace_path">';
    const LINE       = '<td class="trace_line">';
    const CLASS_NAME = '<td class="trace_class">';
    const N_SPACE    = '<td class="trace_name_space">';
    const FUNC       = '<td class="trace_function">';
    const ARGS       = '<td class="trace_args">';
    const NUM        = '<td class="trace_args numeric">';
    const TAG_END    = '">';
    const STRING     = '<td class="trace_args string" title="';
    const ARRAY      = '<td class="trace_args array" title="';
    const BOOL       = '<td class="trace_args bool">';
    const STRING_END   = '<span class="trace_args end">...</span>';
    const SPAN         = '</span>';
    const S_CLASS_NAME = '<span class="trace_class">';
    const S_N_SPACE    = '<span class="trace_name_space">';
    const TABLE        = '<table class="micro_trace">';
    const TABLE_END    = '</table>';
    const EMPTY_ARGS   = '<td class="trace_args empty"></td>';
    const STRIPE       = '<tr class="color';

    protected function before()
    {
        //TODO валидацю массива настроек
        !is_int(${0} = $this->settings->get('stringLength'))
            ?: $this->stringLength = ${0};
    }

    protected function file(string $file, &$arr)
    {
        $parts = explode('/', $file);
        //получаем имя файла без пути
        $arr['file'] = static::FILE.array_pop($parts).static::TD;

        //получаем путь (уже без имени файла) относительно корня приложения для экономии пространства в таблице
        $path = str_replace($this->settings->appDir().'/', '', implode('/', $parts).'/');
        $arr['path'] = static::PATH.$path.static::TD;
    }

    protected function line(int $line, array &$arr)
    {
        $arr['line'] = static::LINE.$line.static::TD;
    }

    protected function className(string $class, array &$arr)
    {
        //получаем имя класса без пространства имён
        $parts = explode('\\', $class);
        $arr['class'] = static::CLASS_NAME.array_pop($parts).static::TD;

        //получаем пространство имён без имени класса
        $parts[] = '';
        $arr['nameSpace'] = static::N_SPACE.implode('\\', $parts).static::TD;
    }

    protected function functionName(string $func, array &$arr)
    {
        $arr['function'] = static::FUNC.$func.static::TD;
    }

    protected function objectArg($arg): string
    {
        $parts = explode('\\', get_class($arg));

        //имя класса без пространства имён
        $obj = static::S_CLASS_NAME.array_pop($parts).static::SPAN;

        //пространство имён без имени класса
        $space = static::S_N_SPACE.implode('\\', $parts).'\\'.static::SPAN;
        return
            static::ARGS.$space.$obj.static::TD;
    }

    protected function arrayArg($arg): string
    {
        //формируем текстовый предпросмотр массива в title
        ob_start();
        print_r($arg);
        $title = mb_substr(ob_get_clean(), 0, $this->titleLength);
        $title = htmlentities($title, ENT_SUBSTITUTE | ENT_COMPAT);
        $count = '['.count($arg).']';
        return
            static::ARRAY.$title.static::TAG_END.'array'.$count.static::TD;
    }

    protected function stringArg($arg): string
    {
        $length = mb_strlen($arg);
        if ($arg === '') {
            //подмена пустой строки на видимое обозначение
            return static::BOOL.'empty_string'.static::TD;

        } elseif (preg_match('/^\s*$/', $arg)) {
            //подмена пробельной строки на видимое обозначение
            return static::BOOL.'space{'.$length.'}'.static::TD;

        } else {
            $title = 'length = '.$length;
            $arg = htmlentities($arg, ENT_SUBSTITUTE | ENT_COMPAT);

            if ($length > $this->stringLength) {
                //обрезаем строку для ячейки таблицы и для предпросмотра в title
                $title .= ' --> '.mb_substr($arg, 0, $this->titleLength);
                $arg = mb_substr($arg, 0, $this->stringLength);
                $end = static::STRING_END;
            } else {
                $end = '';
            }
            return static::STRING.$title.static::TAG_END.$arg.$end.static::TD;
        }
    }

    protected function numericArg($arg): string
    {
        return static::NUM.$arg.static::TD;
    }

    protected function boolArg($arg): string
    {
        $arg = $arg === true ? 'true' : 'false';
        return static::BOOL.$arg.static::TD;
    }

    protected function nullArg(): string
    {
        return static::BOOL.'null'.static::TD;
    }

    protected function otherArg($arg): string
    {
        return static::ARGS.$arg.static::TD;
    }

    protected function completion(): string
    {
        $l = 1; // полосатая таблица
        $trace = '';
        $trace .= static::TABLE;
        foreach ($this->arr as $v) {
            $trace .= static::STRIPE.($l *= -1).static::TAG_END
                .$v['path'].$v['line'].$v['file'].$v['nameSpace'].$v['class'].$v['function'];

            isset($v['args']) ?: $v['args'] = [];

            for ($k = 0; $k < $this->maxNumberOfArgs; ++$k) {
                $trace .= $v['args'][$k] ?? static::EMPTY_ARGS;
            }
        }
        return $trace .= static::TABLE_END;
    }

}