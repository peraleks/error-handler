<?php
declare(strict_types=1);

namespace MicroMir\Error\Exception;


class ErrorHandlerException extends \Exception
{
    public function __construct(string $message)
    {
        $this->message = $message;
    }
}