<?php

namespace MicroMir\Error\Notifiers;


use MicroMir\Error\Trace\CliTraceHandler;

class CliNotifier extends AbstractNotifier
{
        //TODO
    const ERROR      = "\033[30;41m";
    const WARNING    = "\033[31;43m";
    const NOTICE     = "\033[1;30;43m";
    const PARSE      = "\033[45m";
    const DEPRECATED = "\033[30;47m";
    const CYAN       = "\033[0;36m";
    const GRAY       = "\033[0;37m";

    public $codeColor = [
        E_ERROR             => self::ERROR,
        E_CORE_ERROR        => self::ERROR,
        E_COMPILE_ERROR     => self::ERROR,
        E_USER_ERROR        => self::ERROR,
        E_RECOVERABLE_ERROR => self::ERROR,
        'NCE'               => self::ERROR,

        E_WARNING           => self::WARNING,
        E_CORE_WARNING      => self::WARNING,
        E_COMPILE_WARNING   => self::WARNING,
        E_USER_WARNING      => self::WARNING,

        E_PARSE             => self::PARSE,

        E_NOTICE            => self::NOTICE,
        E_USER_NOTICE       => self::NOTICE,

        E_STRICT            => self::DEPRECATED,
        E_DEPRECATED        => self::DEPRECATED,
        E_USER_DEPRECATED   => self::DEPRECATED,
    ];

    protected function display()
    {
        $rst     = "\033[0m";
        $code    = $this->obj->getCode();
        $eName   = $this->obj->getName();
        $file    = $this->obj->getFile();
        $line    = $this->obj->getLine();
        $message = $this->obj->getMessage();

        echo
            "\n".$this->codeColor[$code]."[$code] $eName $rst".self::CYAN." $file::$line $rst \n"
            .self::GRAY.$message.$rst."\n\n";

        if (!true) return;

        $trace = (new CliTraceHandler($this->obj->getTrace(), $this->settings));


    }

}