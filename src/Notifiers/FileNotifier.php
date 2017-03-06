<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;


use Peraleks\ErrorHandler\Exception\PropertyMustBeDefinedException;
use Peraleks\ErrorHandler\Exception\PropertyTypeException;
use Peraleks\ErrorHandler\Trace\FileTraceHandler;

class FileNotifier extends AbstractNotifier
{
    protected $timeFormat = 'd-M-o H:i:s O';

    protected function prepare()
    {
        !is_string($t= $this->configObject->get('timeFormat')) ?: $this->timeFormat = $t;
    }

    protected function getTraceHandlerClass(): string
    {
        return FileTraceHandler::class;
    }


    public function notify()
    {
        if (!$file = $this->configObject->get('file')) {
            throw new PropertyMustBeDefinedException('file');
        }
        if (!is_string($file)) {
            throw new PropertyTypeException($file, 'file', 'string');
        }
        $fileRes = fopen($this->configObject->get('file'), 'ab');
        if (!$fileRes) {
            return;
        }
        fwrite($fileRes, $this->finalStringError);
        fclose($fileRes);
    }

    protected function ErrorToString(string $trace): string
    {
        $e = $this->errorObject;
        '' == $trace ?: $trace = "\nStack trace:\n".$trace;

        return
        "\n[".$this->time().'] ['.$e->getCode().'] '.$e->getType().':  '.$e->getMessage()
        .' in '.$e->getFile().' ('.$e->getLine().')'.$trace;
    }

    protected function time()
    {
        return date($this->timeFormat);
    }
}