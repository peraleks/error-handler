<?php

namespace MicroMir\Error\Trace;


class HttpTraceHandler extends AbstractTraceHandler
{
    private $countArgs;

    private $strLength = 80;

    const TD         = '</td>';
    const SPAN       = '</span>';
    const FILE       = '<td class="trace_file">';
    const PATH       = '<td class="trace_path">';
    const LINE       = '<td class="trace_line">';
    const CLASS_NAME = '<td class="trace_class">';
    const N_SPACE    = '<td class="trace_name_space">';
    const FUNC       = '<td class="trace_function">';
    const ARGS       = '<td class="trace_args">';
    const ARRAY      = '<td class="trace_args array">';
    const BOOL      = '<td class="trace_args bool">';
    const STRING_END = '<span class="trace_args end">...</span>';
    const S_CLASS_NAME = '<span class="trace_class">';
    const S_N_SPACE    = '<span class="trace_name_space">';

    protected function fileName(int $i, string $separator)
    {
        $fileParts = explode('/', $this->dBTrace[$i]['file']);

        //получаем имя файла без пути и расширения
        $this->arr[$i]['file'] = self::FILE.rtrim(array_pop($fileParts), '.php').self::TD;

        //получаем путь файла без имени и обрезаем путь до веб-папки для экономии пространства в таблице
        $path = str_replace($this->webDir.'/', '', implode('/', $fileParts));
        $this->arr[$i]['path'] = self::PATH.$path.$separator.self::TD;
    }

    protected function line(int $i)
    {
        $this->arr[$i]['line'] = self::LINE.$this->dBTrace[$i]['line'].self::TD;
    }

    protected function className(int $i, string $separator)
    {
        //получаем имя класса без пространства имён
        $classParts = explode('\\', $this->dBTrace[$i]['class']);
        $this->arr[$i]['class'] = self::CLASS_NAME.array_pop($classParts).self::TD;

        //получаем пространство имён без имени класса
        $this->arr[$i]['nameSpace'] = self::N_SPACE.implode('\\', $classParts).$separator.self::TD;
    }

    protected function functionName(int $i)
    {
        $this->arr[$i]['function'] = self::FUNC.$this->dBTrace[$i]['function'].self::TD;
    }

    protected function objectArg(int $i, $arg)
    {
        $objectParts = explode('\\', get_class($arg));

        //имя класса без пространства имён
        $obj = self::S_CLASS_NAME.array_pop($objectParts).self::SPAN;

        //пространство имён без имени класса
        $space = self::S_N_SPACE.implode('\\', $objectParts).'\\'.self::SPAN;

        $this->arr[$i]['args'][] = self::ARGS.$space.$obj.self::TD;
    }

    protected function arrayArg(int $i)
    {
        $this->arr[$i]['args'][] = self::ARRAY.'[array]'.self::TD;
    }

    protected function stringArg(int $i, string $arg)
    {
        //обрезаем строку для таблицы
        mb_strlen($arg) > $this->strLength
            ? $end = self::STRING_END
            : $end = '';
        $str = htmlentities(mb_substr($arg, 0, $this->strLength), ENT_SUBSTITUTE).$end;
        $this->arr[$i]['args'][] = self::ARGS.$str.self::TD;
    }

    protected function boolArg(int $i, string $arg)
    {
        $arg == true ? $arg = 'true' : $arg = 'false';
        $this->arr[$i]['args'][] = self::BOOL.$arg.self::TD;
    }

    protected function nullArg(int $i)
    {
        $this->arr[$i]['args'][] = self::BOOL.'null'.self::TD;
    }

    protected function otherArg(int $i, string $arg)
    {
        $this->arr[$i]['args'][] = self::ARGS.$arg.self::TD;
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
        $this->traceResult .= '<table class="micro_trace">';
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
                    $this->traceResult .= '<td class="trace_args"></td>';
                }
            }
        }
        $this->traceResult .= '</table>';
    }

}