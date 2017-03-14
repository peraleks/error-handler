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

namespace Peraleks\ErrorHandler\Notifiers;


use Peraleks\ErrorHandler\Exception\PropertyMustBeDefinedException;
use Peraleks\ErrorHandler\Exception\PropertyTypeException;
use Peraleks\ErrorHandler\Trace\FileTraceFormatter;

/**
 * Class FileNotifier
 *
 * Форматирует и выводит ошибку в файл (логирует).
 */
class FileNotifier extends AbstractNotifier
{
    /**
     * Формат времени для PHP date().
     *
     * @var string
     */
    protected $timeFormat = 'd-M-o H:i:s O';

    /**
     * Полное имя файла лога.
     *
     * @var string
     */
    protected $file;

    /**
     * Валидирует параметры конфигурации - 'file' и 'timeFormat'.
     *
     * @throws PropertyMustBeDefinedException
     * @throws PropertyTypeException
     */
    protected function before()
    {
        if (!$this->file = $this->configObject->get('file')) {
            throw new PropertyMustBeDefinedException('file');
        }
        if (!is_string($this->file)) {
            throw new PropertyTypeException($this->file, 'file', 'string');
        }
        !is_string($t = $this->configObject->get('timeFormat')) ?: $this->timeFormat = $t;
    }

    /**
     * Возвращает имя класса обработчика стека вызовов.
     *
     * @return string FileTraceHandler::class
     */
    protected function traceFormatterClass(): string
    {
        return FileTraceFormatter::class;
    }

    /**
     * Форматирует ошибку для записи в файл ввиде строки.
     *
     * @param string $trace стек вызовов
     * @return string ошибка в формате строки
     */
    protected function ErrorToString(string $trace): string
    {
        $eObj = $this->errorObject;

        if ($trace) {
            $dir = '';
            if (!$this->configObject->get('phpNativeTrace')) {
                $appDir = $this->configObject->getAppDir();
                $fullFile = $eObj->getFile();
                $file = preg_replace('#^'.$appDir.'#', '', $fullFile);
                $dir = $fullFile === $file ? '' : "\n(".$appDir.')';
            }
            $trace = "Stack trace:".$dir."\n".$trace."\n";
        }

        return
        "\n[".$this->time().'] ['.$eObj->getCode().'] '.$eObj->getType().':  '.$eObj->getMessage()
        .' in '.$eObj->getFile().' ('.$eObj->getLine().')'."\n".$trace;
    }

    /**
     * Пишет ошибку в файл.
     *
     * @param string $error форматированная ошибка
     * @return void
     */
    protected function notify(string $error)
    {
        $fileRes = fopen($this->file, 'ab');
        if (!$fileRes) {
            return;
        }
        fwrite($fileRes, $error);
        fclose($fileRes);
    }

    /**
     * Возвращает текущее время в формате,
     * заданном в файле конфигурации ('timeFormat')
     *
     * @return false|string
     */
    protected function time()
    {
        return date($this->timeFormat);
    }
}