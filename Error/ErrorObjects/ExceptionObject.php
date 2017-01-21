<?php

namespace MicroMir\Error\ErrorObjects;


class ExceptionObject extends AbstractErrorObject
{
    public function __construct(array $dBTrace)
    {
        $args          = $dBTrace[0]['args'][0];
        $this->code    = $args->getCode();
        $this->message = $args->getMessage();
        $this->file    = $args->getFile();
        $this->line    = $args->getLine();

        if ($args instanceof \Error) {
            $this->code = 1;
        } elseif ($args instanceof \ParseError) {
            $this->code = 4;
        } elseif ($args instanceof \Exception && $this->code == 0) {
            $this->code = 3;
        }
        $this->name = self::getErrorName($this->code);

        $traceArr = [];
        $traceArr[0]['file'] = $this->file;
        $traceArr[0]['line'] = $this->line;
        $this->trace = ($this->getTraceHandler())->handle(array_merge($traceArr, $args->getTrace()));

    }
}