<?php


namespace Peraleks\ErrorHandler\Exception;


trait ExceptionSourceNameTrait
{
    public function exceptionSourceName()
    {
        $file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['file'];
        preg_match('/^.*\/(.+)\..+$/' ,$file, $arr);
        return $arr[1];
    }
}