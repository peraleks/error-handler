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

/**
 * Class TailNotifier
 *
 * Форматирует в цвете и выводит результат в файл.
 * Для просмотра в терминале используйте команду tail -f
 */
class TailNotifier extends CliNotifier
{
    const REPEAT = "\033[1;30m%s\033[0m";
    const DATE   = "\033[33m%s\033[0";

    /**
     * Формат времени для PHP date().
     *
     * @var string
     */
    protected $timeFormat = 'H:i:s';

    /**
     * Валидирует параметры конфигурации - 'file' и 'timeFormat'.
     *
     * @throws PropertyMustBeDefinedException
     * @throws PropertyTypeException
     */
    protected function before()
    {
        if (!$file = $this->configObject->get('file')) {
            throw new PropertyMustBeDefinedException('file');
        }
        if (!is_string($file)) {
            throw new PropertyTypeException($file, 'file', 'string');
        }
        !is_string($t= $this->configObject->get('timeFormat')) ?: $this->timeFormat = $t;
        parent::before();
    }

    /**
     * Выполняет вывод подготовленной ошибки в файл.
     *
     * Создаёт дополнительный файл, где хранит crc32 хеш
     * последней ошибки для отслеживания повторов.
     *
     * @param string $error форматированная ошибка
     * @return void
     */
    protected function notify(string $error)
    {
        $file = $this->configObject->get('file');
        $fileRepeat = $file.'.repeat';

        if (!file_exists($fileRepeat)) {
            file_put_contents($fileRepeat, '');
        }
        $fileRepeatResource = fopen($fileRepeat, 'rb');
        if (!$fileRepeatResource) {
            return;
        }

        $a = crc32($error);
        $b = (int)fread($fileRepeatResource, 12);
        if ($a == $b) {
            $error = $this->time().sprintf(static::REPEAT, '>>repeat ');
        } else {
            $fileRepeatResource = fopen($fileRepeat, 'wb');
            if (!$fileRepeatResource) {
                return;
            }
            fwrite($fileRepeatResource, (string)crc32($error));
            $error = "\n".$this->time().' '.$error."\n";
        }
        fclose($fileRepeatResource);

        $fileResource = fopen($file, 'ab');
        if (!$fileResource) {
            return;
        }
        fwrite($fileResource, $error);
        fclose($fileResource);
    }

    /**
     * Возвращает текущее время в формате,
     * заданном в файле конфигурации ('timeFormat')
     *
     * @return false|string
     */
    protected function time(): string
    {
        return sprintf(static::DATE, date($this->timeFormat));
    }
}
