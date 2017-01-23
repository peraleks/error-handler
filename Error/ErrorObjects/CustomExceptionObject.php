<?php
/**
 * Created by PhpStorm.
 * User: X
 * Date: 22.01.2017
 * Time: 0:22
 */

namespace MicroMir\Error\ErrorObjects;


class CustomExceptionObject extends AbstractErrorObject
{
    public function __construct(array $dBTrace)
    {
        $trace   = $obj->getTrace();
        $message = $obj->getMessage();

        if (is_string($traceNumber)) {
            $arr = explode('::', $traceNumber);
            $file = $arr[0];
            $line = isset($arr[1]) ? $arr[1] : '';
            $traceNumber = 0;

        } elseif (isset($trace[$traceNumber]['file'])) {
            $file = $trace[$traceNumber]['file'];
            $line = $trace[$traceNumber]['line'];
        } else {
            $file = '';
            $line = '<-';
        }
        $this->traceHandler($trace, $traceNumber);

        $this->notify(
            $obj->getCode(),                    // code
            'Micro_Exception',                  // name
            $message['displayError'],           // message
            $message['logError'],               // log message
            $file,
            $line
        );
    }

}