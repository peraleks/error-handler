<?php

namespace MicroMir\Error\ErrorObjects;


class ExceptionObject extends AbstractErrorObject
{
    public function __construct(array $dBTrace)
    {
        $obj = $dBTrace[0]['args'][0];
        \d::d($obj);
        $this->code    = $obj->getCode();
        $this->message = $obj->getMessage();
        $this->file    = $obj->getFile();
        $this->line    = $obj->getLine();

        if ($obj instanceof \ParseError) $this->code = E_PARSE;
        elseif ($obj instanceof \Error) $this->code = E_ERROR;
        elseif ($obj instanceof \Exception && $this->code == 0) $this->code = 3;

        $this->name    = self::getErrorName($this->code);
        $this->trace   = $obj->getTrace();

    }
}