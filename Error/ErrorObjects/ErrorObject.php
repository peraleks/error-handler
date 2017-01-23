<?php

namespace MicroMir\Error\ErrorObjects;


class ErrorObject extends AbstractErrorObject
{
    public function __construct(array $dBTrace)
    {
        $args          = $dBTrace[0]['args'];
        $this->code    = $args[0];
        $this->name    = self::getErrorName($this->code);
        $this->message = $args[1];
        $this->file    = $args[2];
        $this->line    = $args[3];
        $this->trace   = $dBTrace;
    }
}