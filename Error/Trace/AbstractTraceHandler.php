<?php

namespace MicroMir\Error\Trace;


use MicroMir\Error\Settings;

abstract class AbstractTraceHandler
{
    protected $dBTrace;

    protected $traceResult;

    protected $arr;

    protected $settings;

    public function __construct(array $dBTrace, Settings $settings)
    {
        $this->dBTrace = $dBTrace;
        $this->settings = $settings;
        $this->handleTrace();

    }

    protected function handleTrace()
    {
        for ($i = 0; $i < count($this->dBTrace); ++$i) {
            //обработка имени файла
            $this->missingKey($i, 'file');
            $this->fileName($i);

            //обработка номера строки
            $this->missingKey($i, 'line');
            $this->line($i);

            //обработка имени класса
            $this->missingKey($i, 'class');
            $this->className($i);

            //обработка имени функции
            $this->missingKey($i, 'function');
            $this->functionName($i);

            //обработка аргументов
            $this->arr[$i]['args'] = [];
            //TODO обрезание trace до вызова обработчика
            if (!empty($this->dBTrace[$i]['class']) || $this->dBTrace[$i]['class'] != __CLASS__) {
                !$this->missingKey($i, 'args') ?: $this->dBTrace[$i]['args'] = [];
            }
            foreach ($this->dBTrace[$i]['args'] as $arg) {
                    if (is_object($arg)) $this->objectArg($i, $arg);
                elseif (is_array($arg))  $this->arrayArg($i, $arg);
                elseif (is_string($arg)) $this->stringArg($i, $arg);
                elseif (is_numeric($arg))$this->numericArg($i, $arg);
                elseif (is_bool($arg))   $this->boolArg($i, $arg);
                elseif (is_null($arg))   $this->nullArg($i);
                else $this->otherArg($i, $arg);
            }
            //подсчёт наибольшего количеста аргументов
            $this->countArgs($i);
        }
        //подготовка html таблицы
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

    abstract protected function fileName(int $i);

    abstract protected function line(int $i);

    abstract protected function className(int $i);

    abstract protected function functionName(int $i);

    abstract protected function objectArg(int $i, $arg);

    abstract protected function arrayArg(int $i, $arg);

    abstract protected function stringArg(int $i, $arg);

    abstract protected function numericArg(int $i, $arg);

    abstract protected function boolArg(int $i, $arg);

    abstract protected function nullArg(int $i);

    abstract protected function otherArg(int $i, $arg);

    abstract protected function countArgs(int $i);

    abstract protected function httpTable();


    public function result(): string
    {
        return $this->traceResult;
    }
}