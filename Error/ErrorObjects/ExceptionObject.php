<?php

namespace MicroMir\Error\ErrorObjects;


class ExceptionObject extends AbstractErrorObject
{
    public function __construct(array $dBTrace)
    {
        $obj = $dBTrace[0]['args'][0];
        $this->code    = $obj->getCode();
        $this->message = $obj->getMessage();
        $this->file    = $obj->getFile();
        $this->line    = $obj->getLine();

            if ($obj instanceof \Error) $this->code = E_ERROR;
        elseif ($obj instanceof \ParseError) $this->code = E_PARSE;
        elseif ($obj instanceof \Exception && $this->code == 0) $this->code = 'NCE';

        $this->name = self::getErrorName($this->code);

        $arr = [];
        $arr[0]['file'] = $this->file;
        $arr[0]['line'] = $this->line;
        $this->trace = array_merge($arr, $obj->getTrace());

    }
}