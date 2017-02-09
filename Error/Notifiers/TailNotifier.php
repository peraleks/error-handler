<?php

namespace MicroMir\Error\Notifiers;


class TailNotifier extends CliNotifier
{
    const REPEAT = "\033[31m%s\033[0";
    const DATE   = "\033[33m%s\033[0";

    protected function prepare(): string
    {
        if (!$file = $this->settings->get('file')) {
            throw new \Exception(__CLASS__.' The file is not defined');
        }
        if (!is_string($file)) {
            throw new \Exception('Wrong name of the settings file');
        }
        return parent::prepare();
    }


    public function notify(string $notice)
    {
        $file = $this->settings->get('file');
        $fileRep = $file.'.repeat';

        $fileRepRes = fopen($fileRep, 'r+b');
        if (!$fileRepRes) return;

        if (file_exists($file)) {
            $a = crc32($notice);
            $b = (int)fread($fileRepRes, 12);
            if ($a == $b) {
                $notice = $this->time().sprintf(static::REPEAT, '>>repeat ');
            } else {
                fwrite($fileRepRes, crc32($notice));
                $notice = "\n".$this->time().' '.$notice;
            }
        } else {
            fwrite($fileRepRes, crc32($notice));
        }
        fclose($fileRepRes);

        $fileRes = fopen($file, 'ab');
        if (!$fileRes) return;
        fwrite($fileRes, $notice);
        fclose($fileRes);
    }

    protected function time(): string
    {
        return sprintf(static::DATE, date('H:i:s'));
    }

}