<?php

namespace MicroMir\Error\Trace;


abstract class AbstractTraceHandler
{
    protected $dBTrace;

    protected $traceResult;

    protected $arr;

    protected $webDir;

    public function __construct(array $dBTrace, string $webDir = '')
    {
        $this->dBTrace = $dBTrace;
        $this->webDir = $webDir;
        $this->handleTrace();

    }

    protected function handleTrace()
    {
        for ($i = 0; $i < count($this->dBTrace); ++$i) {
            //обработка имени файла
            $this->missingKey($i, 'file')
                ? $separator = ''
                : $separator = '/';
            $this->fileName($i, $separator);

            //обработка номера строки
            $this->missingKey($i, 'line');
            $this->line($i);

            //обработка имени класса
            $this->missingKey($i, 'class')
                ? $separator = ''
                : $separator = '\\';
            $this->className($i, $separator);

            //обработка имени функции
            $this->missingKey($i, 'function');
            $this->functionName($i);

            //обработка аргументов
            $this->arr[$i]['args'] = [];
            if (!empty($this->dBTrace[$i]['class']) || $this->dBTrace[$i]['class'] != __CLASS__) {
                !$this->missingKey($i, 'args') ?: $this->dBTrace[$i]['args'] = [];
            }
            foreach ($this->dBTrace[$i]['args'] as $arg) {
                    if (is_object($arg)) $this->objectArg($i, $arg);
                elseif (is_array($arg))  $this->arrayArg($i);
                elseif (is_string($arg)) $this->stringArg($i, $arg);
                elseif (is_bool($arg))   $this->boolArg($i, $arg);
                elseif (is_null($arg))   $this->nullArg($i);
                else $this->otherArg($i, $arg);
            }
            //подсчёт наибольшего количеста аргументов
            $this->countArgs($i);
        }
        //подготовка http таблицы
        $this->httpTable();
    }

    protected function missingKey($i, $key)
    {
        if (!isset($this->dBTrace[$i][$key])) {
            $this->dBTrace[$i][$key] = '';
            return true;
        }
        return false;
    }

    abstract protected function fileName(int $i, string $separator);

    abstract protected function line(int $i);

    abstract protected function className(int $i, string $separator);

    abstract protected function functionName(int $i);

    abstract protected function objectArg(int $i, $arg);

    abstract protected function arrayArg(int $i);

    abstract protected function stringArg(int $i, string $arg);

    abstract protected function boolArg(int $i, string $arg);

    abstract protected function nullArg(int $i);

    abstract protected function otherArg(int $i, string $arg);

    abstract protected function countArgs(int $i);

    abstract protected function httpTable();


    public function result(): string
    {
        return $this->traceResult;
    }
}