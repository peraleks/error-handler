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

/**
 * Class CliTraceFormatter
 *
 * Форматирует стек вызовов для отображения в CLI режиме.
 */
class CliTraceFormatter extends AbstractTraceFormatter
{
                            /* цвет */
    const FILE       = "\033[0;36m%s\033[0m";
    const LINE       = "\033[0;36m%s\033[0m";
    const CLASS_NAME = "\033[37m%s\033[0m";
    const FUNC       = "\033[33m%s\033[0m";
    const TYPE       = "\033[1;30m%s\033[0m";
    const OBJ        = "\033[1;30m%s\033[0m";
    const CALL       = "\033[0;34m%s\033[0m";
    const ARR        = "\033[35m%s\033[0m";
    const STRING     = "\033[32m%s\033[0m";
    const TRIM       = "\033[1;30m%s\033[0m";
    const NUM        = "\033[1;34m%s\033[0m";
    const BOOL       = "\033[31m%s\033[0m";
    const TRACE_CNT  = "\033[1;30m%s\033[0m";

    /**
     * Выравнивание типов параметров функции относительно левого края окна.
     *
     * @var int
     */
    protected $align = 15;

    /**
     * Максимальная длянна строки при показе содержимого
     * строковых параметров и массивов.
     *
     * @var int
     */
    protected $stringLength = 80;

    /**
     * Валидирует параметр конфигурации 'stringLength'.
     */
    protected function before()
    {
        !is_int($length = $this->configObject->get('stringLength')) ?: $this->stringLength = $length;
    }

    /**
     * Здесь производим окончательное форматирование массива стека вызовов
     * и формируем конечную строку.
     *
     * @param array $traceArray предварительно отформатированный стек
     * @return string окончательный результат обработки стека
     */
    protected function completion(array $traceArray): string
    {
        $trace = '';
        for ($i = 0, $c = count($traceArray); $i < $c; ++$i) {
            $v =& $traceArray[$i];
            $trace .= sprintf(static::TRACE_CNT, '#'.$i).$v['file'].$v['line'].$v['class'].$v['function'];

            foreach ($v['args'] as $arg) {
                $trace .= "\n".$arg;
            }
            $trace .= "\n".sprintf(static::FUNC, $this->space(')', 3))."\n";
        }
        return $trace;
    }

    /**
     * Возвращает дополненную пробелами строку.
     *
     * Используется для выравнивания правого края строки типов аргументов
     * относительно левого края окна для улучшения вертикального восприятия.<br>
     * Если второй параметр не передан, то строка будет дополнена
     * до длинны взятой из $this->align.
     *
     * @param string   $string
     * @param int|null $align  до какой длинны дополнять
     * @return string выровненная строка
     */
    protected function space(string $string, int $align = null)
    {
        return sprintf("%".($align ?? $this->align)."s", $string).' ';
    }

    /**
     * Возвращает форматированное имя файла.
     *
     * @param string $file полное имя файла
     * @return string
     */
    protected function file(string $file): string
    {
        return sprintf(static::FILE, preg_replace('#^'.$this->configObject->getAppDir().'#', '', $file));
    }

    /**
     * Возвращает форматированный номер строки ошибки.
     *
     * @param int $line номер строки
     * @return string
     */
    protected function line(int $line): string
    {
        $line = 0 === $line ? '[internal function] ' : '('.(string)$line.') ';
        return sprintf(static::LINE, $line);
    }

    /**
     * Возвращает форматированное имя класса и тип вызова метода.
     *
     * @param string $class имя класса
     * @param string $type  тип вызова метода (:: | ->)
     *
     * @return string
     */
    protected function className(string $class, string $type): string
    {
        '' !== $type ?: $type = '  ';
        return sprintf(static::CLASS_NAME, $class."\n".sprintf(static::TYPE, $type));
    }

    /**
     * Возвращает форматированное имя функции и количество аргументов.
     *
     * @param string $function имя метода или функции
     * @param string $param    пустая строка или строка вида 'a.b'
     *                         где a - количество аргументов функции,
     *                         b - количество обязателных аргументов
     * @param string $doc      PHPDoc
     * @return string
     */
    protected function functionName(string $function, string $param, string $doc): string
    {
        $param === '' ?: $param = '{'.$param.'}';

        return sprintf(static::FUNC, $function.$param.'(');
    }

    /**
     * Возвращает форматированное значение строкового аргумента.
     *
     * @param string $arg значение строкового аргумента
     * @return string
     */
    protected function stringArg($arg): string
    {
        $length = mb_strlen($arg);
        $type = sprintf(static::TYPE, $this->space('['.$length.']str'));
        $str = sprintf(static::STRING, mb_substr($arg, 0, $this->stringLength));
        if ($length > $this->stringLength) {
            $str .= sprintf(static::TRIM, '...');
        }
        return $type.$str;
    }

    /**
     * Возвращает форматированное значение числового аргумента.
     *
     * @param int|float $arg значение числового аргумента
     * @return string
     */
    protected function numericArg($arg): string
    {
        return sprintf(static::TYPE, $this->space('num')).sprintf(static::NUM, $arg);
    }

    /**
     * Возвращает форматированный аргумент массив.
     *
     * @param array $arg массив
     * @return string
     */
    protected function arrayArg($arg): string
    {
        $preview = '';
        foreach ($arg as $key => ${0}) {
            $preview .= '['.$key.']=>..., ';
            if (mb_strlen($preview) > $this->stringLength) {
                break;
            }
        }
        return sprintf(static::TYPE, $this->space('array['.count($arg).']')).sprintf(static::ARR, $preview);
    }

    /**
     * Возвращает форматированное значение аргумента null.
     *
     * @return string форматированное значение 'null'
     */
    protected function nullArg(): string
    {
        return sprintf(static::TYPE, $this->space('null'));
    }

    /**
     * Возвращает форматированное булево значение аргумента.
     *
     * @param bool $arg
     * @return string форматированное 'true' | 'false'
     */
    protected function boolArg($arg): string
    {
        $arg = $arg === true ? 'true' : 'false';
        return sprintf(static::TYPE, $this->space('bool')).sprintf(static::BOOL, $arg);
    }

    /**
     * Возвращает форматированное значение аргумента object.
     *
     * @param object $arg значение аргумента object
     * @return string
     */
    protected function objectArg($arg): string
    {
        return sprintf(static::TYPE, ($this->space('obj'))).sprintf(static::OBJ, get_class($arg));
    }

    /**
     * Возвращает форматированное значение аргумента callable.
     *
     * @param \Closure $arg значение аргумента callable
     * @return string
     */
    protected function callableArg($arg): string
    {
        $r = new \ReflectionFunction($arg);
        $start = $r->getStartLine();
        $fileName = $r->getFileName();

        /* первая строка кода функции*/
        $code = implode(array_slice(file($fileName), $start - 1, 1));
        /* убираем пустую строку */
        $code = str_replace(["\r\n", "\n", "\r"], '', $code);

        $thisCl = $r->getClosureThis();
        if (is_object($thisCl)) {
            $thisCl = get_class($thisCl);
        }
        return sprintf(static::TYPE, $this->space('callable'))
            .sprintf(static::CALL, 'this: '.$thisCl)
            .sprintf(static::CALL, ' '.$fileName.'('.$start.')')
            .sprintf(static::CALL, mb_substr("\n".$code, 0, $this->stringLength + 15));
    }

    /**
     * Возвращает форматированное значение аргумента resource.
     *
     * @param resource $arg значение аргумента resource
     * @return string
     */
    protected function resourceArg($arg): string
    {
        $res = 'resource';
        ob_start();
        echo $arg;
        if (preg_match('/^Resource id (\#\d+)$/', ob_get_clean(), $arr)) {
            $res .= $arr[1];
        }
        $s = stream_get_meta_data($arg);
        $mData = 'wr_type: '.$s['wrapper_type'].', mode: '.$s['mode'].', uri: '.$s['uri'];
        return sprintf(static::TYPE, $this->space($res))
            .sprintf(static::TYPE, $mData);
    }

    /**
     * Возвращает форматированную строку типа 'closed resource #...'
     *
     * @param string $string 'closed resource #...'
     * @return string
     */
    protected function closedResourceArg(string $string): string
    {
        return sprintf(static::TYPE, $this->space($string));
    }

    /**
     * Возвращает форматированное значение аргумента неизвестного типа.
     *
     * @param mixed $arg значение аргумента неизвестного типа
     * @return string форматированное значение аргумента неизвестного типа
     */
    protected function otherArg($arg): string
    {
        return sprintf(static::TYPE, $this->space($this->isClosedResource($arg)));
    }
}
