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

use Peraleks\ErrorHandler\Core\ConfigObject;

/**
 * Class AbstractTraceHandler
 *
 * Определяет шаблонный метод и интерфейс для бработчиков стека вызовов.
 */
abstract class AbstractTraceFormatter implements FormatterInterface
{
    /**
     * Объект конфигурации.
     *
     * @var ConfigObject
     */
    protected $configObject;

    /**
     * Окончателный результат обработки стека вызовов ввиде строки.
     *
     * @var string
     */
    protected $traceResult;

    /**
     * Массив для накопления промежуточных результатов обработки.
     *
     * @var array
     */
    protected $arr = [];

    /**
     * Максимальное количество аргументов функции.
     * Может использоваться для определения количества столбцов таблицы.
     *
     * @var int
     */
    protected $maxNumberOfArgs = 0;

    final public function __construct() {}

    /**
     * Инизиализирует начальные параметры.
     *
     * Запускает шаблонный метод handleTrace()
     *
     * @param array $dBTrace стек вызовов
     * @param ConfigObject $configObject объект конфикурации
     * @return string  форматированный стек вызовов
     */
    final public function getFormattedTrace(array $dBTrace, ConfigObject $configObject): string
    {
        $this->configObject = $configObject;
        $this->before();
        return $this->handleTrace($dBTrace);
    }

    /**
     * Здесь проводим валидацю параметров конфигурации и
     * устанавливаем значения по умолчанию.
     *
     * @return void
     */
    abstract protected function before();

    /**
     * Реализует алгоритм обработки стека вызовов.
     *
     * Шаблонный метод.
     *
     * @param $dBTrace array массив стека вызовов
     * @return string форматированный стек вызовов
     */
    final protected function handleTrace(array $dBTrace): string
    {
        $traceArray = [];
        for ($i = 0, $c = count($dBTrace); $i < $c; ++$i) {
            $arr =& $traceArray[$i];
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
            $funcData = $this->params($func, $dbt['class'] ?? '', count($dbt['args']));
            $arr['function'] = $this->functionName($func, $funcData['param'], $funcData['doc']);

            /* обработка аргументов */
            if (!$this->configObject->get('simpleTrace')) {
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
                    elseif ($cr = $this->isClosedResource($arg)) $args[] = $this->closedResourceArg($cr);
                    else $args[] = $this->otherArg($arg);
                }
                /* подсчёт наибольшего количеста аргументов */
                $cnt = count($arr['args']);
                $this->maxNumberOfArgs > $cnt ?: $this->maxNumberOfArgs = $cnt;
            }
        }
        return $this->completion($traceArray);
    }

    protected function isClosedResource($arg)
    {
        /* определяем является ли тип закрытым ресурсом */
        if ('unknown type' === $type = gettype($arg)) {
            ob_start();
            echo $arg;
            if (preg_match('/^Resource id (\#\d+)$/', ob_get_clean(), $arr)) {
                return 'closed resource '.$arr[1];
            }
        }
        return false;
    }

    protected function params(string $func, string $class, int $cntArgs): array
    {
        if ('' != $class && (1 !== preg_match('/^.*{closure}.*$/', $func))) {
            $ref = new \ReflectionMethod($class, $func);
        } elseif (function_exists($func)) {
            $ref = new \ReflectionFunction($func);
        }
        $p = '';
        $doc = '';
        if (isset($ref)) {
            $param = $ref->getNumberOfParameters();
            $reqParam = $ref->getNumberOfRequiredParameters();
            $c = $reqParam > $cntArgs ? ' unset '.($reqParam - $cntArgs) : '';
            $p = $param.'.'.$reqParam.$c;
            $doc = $ref->getDocComment();
        }
        $arr = [];
        $arr['param'] = $p;
        $arr['doc'] = $doc ? $doc : '';

        return $arr;
    }

    protected function completion(array $arr): string { return ''; }

    /**
     * Форматирует имя файла.
     *
     * @param string $file полное имя файла
     * @return string форматированное имя файла
     */
    protected function file(string $file): string { return $file; }

    /**
     * Форматирует номер строки ошибки.
     *
     * @param int $line номер строки
     * @return string форматирофанный номер строки
     */
    protected function line(int $line): string { return (string)$line; }

    /**
     * Форматирует имя класса и тип вызова метода.
     *
     * @param string $class имя класса
     * @param string $type тип вызова метода (:: | ->)
     * @return string форматированные имя класса и тип вызова метода
     */
    protected function className(string $class, string $type): string
    {
        $string = $class.' '.$type.' ';
        return '  ' === $string ? '' : $string;
    }

    /**
     * Форматирует имя функции и количество аргументов.
     *
     * @param string $function имя метода или функции
     * @param string $param пустая строка или строка вида 'a.b'
     * где a - количество аргументов функции, b - количество обязателных аргументов
     * @return string форматированное название функции и количество аргументов
     */
    protected function functionName(string $function, string $param, string $doc): string
    {
        $param === '' ?: $param = '['.$param.']';
        return $function.$param;
    }

    /**
     * Форматирует значение строкового аргумента.
     *
     * @param $arg string значение строкового аргумента
     * @return string форматированное значение строкового аргумента
     */
    protected function stringArg($arg): string { return ''; }

    /**
     * Форматирует значение числового аргумента.
     *
     * @param $arg int | float значение числового аргумента
     * @return string форматированное значение числового аргумента
     */
    protected function numericArg($arg): string { return ''; }

    /**
     * Форматирует аргумент массив.
     *
     * @param $arg array массив
     * @return string форматированный массив
     */
    protected function arrayArg($arg): string { return ''; }

    /**
     * Форматирует значение аргумента null.
     *
     * @return string форматированное значение null
     */
    protected function nullArg(): string { return ''; }

    /**
     * Форматирует булево значение аргумента.
     *
     * @param $arg bool true | false
     * @return string форматированное true | false
     */
    protected function boolArg($arg): string { return ''; }

    /**
     * Форматирует значение аргумента object.
     *
     * @param $arg object значение аргумента object
     * @return string форматированное значение аргумента object
     */
    protected function objectArg($arg): string { return ''; }

    /**
     * Форматирует значение аргумента callable.
     *
     * @param $arg \Closure значение аргумента callable
     * @return string форматированное значение аргумента callable
     */
    protected function callableArg($arg): string { return ''; }

    /**
     * Форматирует значение аргумента resource.
     *
     * @param $arg resource значение аргумента resource
     * @return string форматированное значение аргумента resource
     */
    protected function resourceArg($arg): string { return ''; }

    /**
     * Форматирует строку типа 'closed resource #...'
     *
     * @param $string string 'closed resource #...'
     * @return string форматированное значение
     */
    protected function closedResourceArg(string $string): string { return $string; }

    /**
     * Форматирует значение аргумента неизвестного типа.
     *
     * @param $arg mixed значение аргумента неизвестного типа
     * @return string форматированное значение аргумента неизвестного типа
     */
    protected function otherArg($arg): string
    {
        return gettype($arg);
    }
}
