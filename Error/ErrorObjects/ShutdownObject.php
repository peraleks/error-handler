<?php

namespace MicroMir\Error\ErrorObjects;


class ShutdownObject extends AbstractErrorObject
{
    public function __construct(array $error)
    {
        $this->code    = $error['type'];
        $this->name    = self::getErrorName($this->code);
        $this->message = $error['message'];
        $this->file    = $error['file'];
        $this->line    = $error['line'];
        $this->trace   = debug_backtrace();
        if ($this->code != E_COMPILE_WARNING) ob_end_clean();

    }
}