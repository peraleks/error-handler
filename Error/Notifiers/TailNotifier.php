<?php

namespace MicroMir\Error\Notifiers;


class TailNotifier extends CliNotifier
{
    const RED    = "\033[31m";
    const YELLOW = "\033[33m";

    public function notify(string $notice)
    {
        $file = $this->settings->get('file');
        $fileRep = $file.'.repeat';

         if (file_exists($file)) {
            $fileRepRes = fopen($fileRep, 'r+');
            $a = crc32($notice);
            $b =(int)fread($fileRepRes, 12);
            if ($a == $b) {
                $notice = ' '.$this->time().static::RED.'>>repeat'.static::RST;
            } else {
                $fileRepRes = fopen($fileRep, 'wb');
                fwrite($fileRepRes, crc32($notice));
                $notice = "\n".$this->time().' '.$notice;
            }
        } else  {
             $fileRepRes = fopen($fileRep, 'wb');
            fwrite($fileRepRes, crc32($notice));
        }
        fclose($fileRepRes);

        $fileRes = fopen($file, 'ab');
        fwrite($fileRes, $notice);
        fclose($fileRes);
    }

    protected function time(): string
    {
        return static::YELLOW.date('h:i:s').static::RST;
    }

}