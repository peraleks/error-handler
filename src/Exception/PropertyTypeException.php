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

namespace Peraleks\ErrorHandler\Exception;

/**
 * Class PropertyTypeException
 *
 * Используется когда надо указать, что параметр конфигурации
 * должен быть определённого типа.
 */
class PropertyTypeException extends ErrorHandlerException
{
    use ExceptionSourceNameTrait;

    /**
     * PropertyTypeException constructor.
     *
     * Форматирует сообщение исключения по шаблону:
     * "{имя уведомителя}: the property value '{$key}'=> must be a {$type}
     * gettype($value) given".<br>
     * Например: TailNotifier: the property value 'file'=> must be a string, integer given.
     *
     *
     * @param string $value полученное значение параметра конфигурации
     * @param string $key ключ массива конфикурации
     * @param string $type ожидаемый тип значения параметра
     */
    public function __construct($value, string $key, string $type)
    {
        $this->message
            = $this->exceptionSourceName().': the property value \''.$key.'\'=> must be a '.$type
        .', '.gettype($value).' given';
    }
}
