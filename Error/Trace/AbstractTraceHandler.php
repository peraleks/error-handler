<?php
declare(strict_types=1);

namespace MicroMir\Error\Trace;

use MicroMir\Error\Core\SettingsInterface;

abstract class AbstractTraceHandler
{
    protected $settings;

    protected $traceResult;

    protected $arr = [];

    protected $maxNumberOfArgs = 0;

    public final function __construct(array $dBTrace, SettingsInterface $settings)
    {
        $this->settings = $settings;
        $this->before();
        $this->handleTrace($dBTrace);
    }

    abstract protected function before();

    protected final function handleTrace($dBTrace)
    {
        for ($i = 0; $i < count($dBTrace); ++$i) {
            $arr =& $this->arr[$i];
            $dbt =& $dBTrace[$i];
            //обработка имени файла
            $arr['file'] = $this->file($dbt['file'] ?? '');
            //обработка номера строки
            $arr['line'] = $this->line($dbt['line'] ?? 0);
            //обработка имени класса
            $arr['class'] = $this->className($dbt['class'] ?? '');
            //обработка имени функции
            isset($dbt['args']) ?: $dbt['args'] = [];
            $func = $dbt['function'] ?? '';
            $arr['function'] = $this->functionName($func, $this->params($func, $dbt['class'] ?? '', count($dbt['args'])));
            //обработка аргументов
            $arr['args'] = [];
            $args =& $arr['args'];
            foreach ($dbt['args'] as $arg) {
                    if (is_string($arg))  $args[] = $this->stringArg($arg);
                elseif (is_numeric($arg)) $args[] = $this->numericArg($arg);
                elseif (is_array($arg))   $args[] = $this->arrayArg($arg);
                elseif (is_bool($arg))    $args[] = $this->boolArg($arg);
                elseif (is_null($arg))    $args[] = $this->nullArg();
                elseif ($arg instanceof \Closure)$args[] = $this->callableArg($arg);
                elseif (is_object($arg))  $args[] = $this->objectArg($arg);
                elseif (is_resource($arg))$args[] = $this->resourceArg($arg);
                else $args[] = $this->otherArg($arg);
            }
            //подсчёт наибольшего количеста аргументов
            $cnt = count($arr['args']);
            $this->maxNumberOfArgs > $cnt ?: $this->maxNumberOfArgs = $cnt;
        }
        //завершающяя обработка (формирование строки)
        $this->traceResult = $this->completion();
    }

    abstract protected function file(string $file): string;

    abstract protected function line(int $line): string;

    abstract protected function className(string $class): string;

    abstract protected function functionName(string $function, string $param): string;

    abstract protected function stringArg($arg): string;

    abstract protected function numericArg($arg): string;

    abstract protected function arrayArg($arg): string ;

    abstract protected function nullArg(): string;

    abstract protected function boolArg($arg): string;

    abstract protected function callableArg($arg): string;

    abstract protected function objectArg($arg): string;

    abstract protected function resourceArg($arg): string ;

    abstract protected function completion(): string ;

    protected function otherArg($arg): string
    {
        return gettype($arg);
    }

    public final function getTrace(): string
    {
        return $this->traceResult;
    }

    protected function isClosedResource($arg): string
    {
        // определяем является ли тип закрытым ресурсом
        if ('unknown type' === $type = gettype($arg)) {
            ob_start();
            echo $arg;
            if (preg_match('/^Resource id (\#\d+)$/', ob_get_clean(), $arr)) $type = 'closed resource '.$arr[1];
        }
        return $type;
    }

    protected function params(string $func, string $class, int $cntArgs): string
    {
        if ('' != $class) {
            $ref = new \ReflectionMethod($class, $func);
        } elseif (function_exists($func)) {
            $ref = new \ReflectionFunction($func);
        }
        $p = '';
        if (isset($ref)) {
            $param = $ref->getNumberOfParameters();
            $reqParam = $ref->getNumberOfRequiredParameters();
            $c = $reqParam > $cntArgs ? ' unset '.($reqParam - $cntArgs) : '';
            $p = $param.'.'.$reqParam.$c;
        }
        return $p;
    }

}
