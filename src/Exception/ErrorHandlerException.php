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
 * Class ErrorHandlerException
 *
 * Исключение для любых ошибок, произошедших внутри обработчика
 * и не подподающих под категории исключений расширяющих данный класс.
 */
class ErrorHandlerException extends \Exception
{
    /**
     * ErrorHandlerException constructor.
     *
     * @param string $message текст сообщения
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
