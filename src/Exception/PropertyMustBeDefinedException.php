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
 * Class PropertyMustBeDefinedException
 *
 * Используется в случае если параметр конфигурации
 * не задан пользователем, но обязательно должен присутствовать.
 */
class PropertyMustBeDefinedException extends ErrorHandlerException
{
    use ExceptionSourceNameTrait;

    /**
     * PropertyMustBeDefinedException constructor.
     *
     * Форматирует сообщение исключения по шаблону:
     * "{имя уведомителя}: the property '{$property}'=> must be defined".
     * <br>
     * Например: "TailNotifier: the property 'file'=> must be defined".
     *
     * @param string $key ключ нассива конфигурации
     */
    public function __construct(string $key)
    {
        $this->message = $this->exceptionSourceName().': the property \''.$key.'\'=> must be defined';
    }
}
