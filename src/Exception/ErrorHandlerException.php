<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Exception;

class ErrorHandlerException extends \Exception
{
    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
