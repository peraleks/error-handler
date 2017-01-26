<?php

namespace MicroMir\Error\Trace;


use MicroMir\Error\Core\SettingsObject;

class HtmlTraceHandler extends AbstractTraceHandler
{
    protected $countArgs;

    protected $stringLength = 80;

    const TD         = '</td>';
    const FILE       = '<td class="trace_file">';
    const PATH       = '<td class="trace_path">';
    const LINE       = '<td class="trace_line">';
    const CLASS_NAME = '<td class="trace_class">';
    const N_SPACE    = '<td class="trace_name_space">';
    const FUNC       = '<td class="trace_function">';
    const ARGS       = '<td class="trace_args">';
    const NUM        = '<td class="trace_args numeric">';
    const TITLE_END  = '">';
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

    public function __construct(array $dBTrace, SettingsObject $settings)
    {
        !is_int(${0} = $settings->get('stringLength'))
            ?: $this->stringLength = ${0};
        parent::__construct($dBTrace, $settings);
    }

    protected function fileName(int $i)
    {
        $fileParts = explode('/', $this->dBTrace[$i]['file']);
        //получаем имя файла без пути
        $this->arr[$i]['file'] = static::FILE.array_pop($fileParts).static::TD;

        //получаем путь (уже без имени файла) относительно корня приложения для экономии пространства в таблице
        $path = str_replace($this->settings->appDir().'/', '', implode('/', $fileParts).'/');
        $this->arr[$i]['path'] = static::PATH.$path.static::TD;
    }

    protected function line(int $i)
    {
        $this->arr[$i]['line'] = static::LINE.$this->dBTrace[$i]['line'].static::TD;
    }

    protected function className(int $i)
    {
        //получаем имя класса без пространства имён
        $classParts = explode('\\', $this->dBTrace[$i]['class']);
        $this->arr[$i]['class'] = static::CLASS_NAME.array_pop($classParts).static::TD;

        //получаем пространство имён без имени класса
        $classParts[] = '';
        $this->arr[$i]['nameSpace'] = static::N_SPACE.implode('\\', $classParts).static::TD;
    }

    protected function functionName(int $i)
    {
        $this->arr[$i]['function'] = static::FUNC.$this->dBTrace[$i]['function'].static::TD;
    }

    protected function objectArg(int $i, $arg)
    {
        $objectParts = explode('\\', get_class($arg));

        //имя класса без пространства имён
        $obj = static::S_CLASS_NAME.array_pop($objectParts).static::SPAN;

        //пространство имён без имени класса
        $space = static::S_N_SPACE.implode('\\', $objectParts).'\\'.static::SPAN;

        $this->arr[$i]['args'][] = static::ARGS.$space.$obj.static::TD;
    }

    protected function arrayArg(int $i, $arg)
    {
        //формируем текстовый предпросмотр массива не болле 2000 символов
        ob_start();
        print_r($arg);
        $preview = mb_substr(ob_get_clean(), 0, 2000);
        $count = '['.count($arg).']';
        $this->arr[$i]['args'][] = static::ARRAY.$preview.static::TITLE_END.'array'.$count.static::TD;
    }

    protected function stringArg(int $i, $arg)
    {
        //подмена пустой строки на видимое обозначение
        if ($arg === '') {
            $this->arr[$i]['args'][] = static::BOOL.'empty_string'.static::TD;
            //подмена пробельной строки на видимое обозначение
        } elseif (preg_match('/^\s*$/', $arg)) {
            $num = '{'.mb_strlen($arg).'}';
            $this->arr[$i]['args'][] = static::BOOL.'space'.$num.static::TD;
        } else {
            $length = mb_strlen($arg);
            if ($length > $this->stringLength) {
                $end = static::STRING_END;
                $arg = htmlentities($arg, ENT_SUBSTITUTE);
                //обрезаем строку для таблицы
                $str = mb_substr($arg, 0, $this->stringLength);
                $title = 'length = '.$length.' --> '.$arg;
            } else {
                $end = '';
                $title = 'length = '.$length;
                $str = htmlentities($arg, ENT_SUBSTITUTE);
            }
            $this->arr[$i]['args'][] = static::STRING.$title.static::TITLE_END.$str.$end.static::TD;
        }
    }

    protected function numericArg(int $i, $arg)
    {
        $this->arr[$i]['args'][] = static::NUM.$arg.static::TD;
    }

    protected function boolArg(int $i, $arg)
    {
        $arg == true ? $arg = 'true' : $arg = 'false';
        $this->arr[$i]['args'][] = static::BOOL.$arg.static::TD;
    }

    protected function nullArg(int $i)
    {
        $this->arr[$i]['args'][] = static::BOOL.'null'.static::TD;
    }

    protected function otherArg(int $i, $arg)
    {
        $this->arr[$i]['args'][] = static::ARGS.$arg.static::TD;
    }

    protected function countArgs(int $i)
    {
        $cnt = count($this->arr[$i]['args']);
        $this->countArgs > $cnt ?: $this->countArgs = $cnt;
    }

    protected function httpTable()
    {
        $l = 1;
        $this->traceResult = '';
        $this->traceResult .= static::TABLE;
        foreach ($this->arr as $value) {
            $this->traceResult
                .= '<tr class="color'.($l = $l * -1).'">'
                .$value['path']
                .$value['line']
                .$value['file']
                .$value['nameSpace']
                .$value['class']
                .$value['function'];

            if (!isset($value['args'])) $value['args'] = [];

            for ($k = 0; $k < $this->countArgs; ++$k) {
                if (array_key_exists($k, $value['args'])) {
                    $this->traceResult .= $value['args'][$k];
                } else {
                    $this->traceResult .= static::EMPTY_ARGS;
                }
            }
        }
        $this->traceResult .= static::TABLE_END;
    }

}