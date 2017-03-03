<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Trace;

use Peraleks\ErrorHandler\Core\ConfigInterface;

abstract class AbstractTraceHandler
{
    protected $configObject;

    protected $traceResult;

    protected $arr = [];

    protected $maxNumberOfArgs = 0;

    final public function __construct(array $dBTrace, ConfigInterface $configObject)
    {
        $this->configObject = $configObject;
        $this->before();
        $this->handleTrace($dBTrace);
    }

    abstract protected function before();

    final protected function handleTrace($dBTrace)
    {
        for ($i = 0; $i < count($dBTrace); ++$i) {
            $arr =& $this->arr[$i];
            $dbt =& $dBTrace[$i];

            /* обработка имени файла */
            $arr['file'] = $this->file($dbt['file'] ?? '');

            /* обработка номера строки */
            $arr['line'] = $this->line($dbt['line'] ?? 0);

            /* обработка имени класса */
            $arr['class'] = $this->className($dbt['class'] ?? '', $dbt['type'] ?? '');

            /* обработка имени функции */
            isset($dbt['args']) ?: $dbt['args'] = [];
            $func = $dbt['function'] ?? '';
            $arr['function'] = $this->functionName(
                $func,
                $this->params($func, $dbt['class'] ?? '', count($dbt['args']))
            );
            if (!$this->configObject->get('simpleTrace')) {
                /* обработка аргументов */
                $arr['args'] = [];
                $args =& $arr['args'];
                foreach ($dbt['args'] as $arg) {
                    if (is_string($arg))       $args[] = $this->stringArg($arg);
                    elseif (is_numeric($arg))  $args[] = $this->numericArg($arg);
                    elseif (is_array($arg))    $args[] = $this->arrayArg($arg);
                    elseif (is_bool($arg))     $args[] = $this->boolArg($arg);
                    elseif (is_null($arg))     $args[] = $this->nullArg();
                    elseif ($arg instanceof \Closure) $args[] = $this->callableArg($arg);
                    elseif (is_object($arg))   $args[] = $this->objectArg($arg);
                    elseif (is_resource($arg)) $args[] = $this->resourceArg($arg);
                    else $args[] = $this->otherArg($arg);
                }
                /* подсчёт наибольшего количеста аргументов */
                $cnt = count($arr['args']);
                $this->maxNumberOfArgs > $cnt ?: $this->maxNumberOfArgs = $cnt;
            }
        }
        /* завершающяя обработка (формирование строки) */
        $this->traceResult = $this->completion();
    }

    protected function file(string $file): string { return $file; }

    protected function line(int $line): string { return (string)$line; }

    protected function className(string $class, string $type): string { return $class.' '.$type; }

    protected function functionName(string $function, string $param): string
    {
        $param === '' ?: $param = '{'.$param.'}';
        return $function.$param;
    }

    protected function stringArg($arg): string { return ''; }

    protected function numericArg($arg): string { return ''; }

    protected function arrayArg($arg): string { return ''; }

    protected function nullArg(): string { return ''; }

    protected function boolArg($arg): string { return ''; }

    protected function callableArg($arg): string { return ''; }

    protected function objectArg($arg): string { return ''; }

    protected function resourceArg($arg): string { return ''; }

    protected function completion(): string { return ''; }

    protected function otherArg($arg): string
    {
        return gettype($arg);
    }

    final public function getTrace(): string
    {
        return $this->traceResult;
    }

    protected function isClosedResource($arg): string
    {
        /* определяем является ли тип закрытым ресурсом */
        if ('unknown type' === $type = gettype($arg)) {
            ob_start();
            echo $arg;
            if (preg_match('/^Resource id (\#\d+)$/', ob_get_clean(), $arr)) {
                $type = 'closed resource '.$arr[1];
            }
        }
        return $type;
    }

    protected function params(string $func, string $class, int $cntArgs): string
    {
        if ('' != $class && '{closure}' != $func) {
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
