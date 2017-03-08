<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;


use Peraleks\ErrorHandler\Exception\PropertyMustBeDefinedException;
use Peraleks\ErrorHandler\Exception\PropertyTypeException;
use Peraleks\ErrorHandler\Trace\FileTraceHandler;

class FileNotifier extends AbstractNotifier
{
    protected $timeFormat = 'd-M-o H:i:s O';

    protected $file;

    protected function prepare()
    {
        if (!$this->file = $this->configObject->get('file')) {
            throw new PropertyMustBeDefinedException('file');
        }
        if (!is_string($this->file)) {
            throw new PropertyTypeException($this->file, 'file', 'string');
        }
        !is_string($t= $this->configObject->get('timeFormat')) ?: $this->timeFormat = $t;
    }

    protected function getTraceHandlerClass(): string
    {
        return FileTraceHandler::class;
    }

    public function notify()
    {
        $fileRes = fopen($this->file, 'ab');
        if (!$fileRes) {
            return;
        }
        fwrite($fileRes, $this->finalStringError);
        fclose($fileRes);
    }

    protected function ErrorToString(string $trace): string
    {
        $eObj = $this->errorObject;

        if ($trace) {
            $dir = '';
            if (!$this->configObject->get('phpNativeTrace')) {
                $appDir = $this->configObject->getAppDir();
                $fullFile = $eObj->getFile();
                $file = preg_replace('#^'.$appDir.'#', '', $fullFile);
                $dir = $fullFile === $file ? '' : "\n(".$appDir.')';
            }
            $trace = "Stack trace:".$dir."\n".$trace."\n";
        }

        return
        "\n[".$this->time().'] ['.$eObj->getCode().'] '.$eObj->getType().':  '.$eObj->getMessage()
        .' in '.$eObj->getFile().' ('.$eObj->getLine().')'."\n".$trace;
    }

    protected function time()
    {
        return date($this->timeFormat);
    }
}