<?php

namespace MicroMir\Error\ErrorObjects;


class FatalErrorObject extends AbstractErrorObject
{
    public function __construct(array $dBTrace)
    {
        if (!$error = error_get_last()) return;

        ob_end_clean();
        $this->code    = $error['type'];
        $this->name    = self::getErrorName($this->code);
        $this->message = $error['message'];
        $this->file    = $error['file'];
        $this->line    = $error['line'];
        $this->trace   = ($this->getTraceHandler())->handle($dBTrace);
    }
}