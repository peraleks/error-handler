<?php
declare(strict_types=1);

namespace MicroMir\Error\Notifiers;


use MicroMir\Error\Core\ErrorObject;
use MicroMir\Error\Core\SettingsInterface;
use MicroMir\Error\Trace\CliTraceHandler;

class CliNotifier extends AbstractNotifier
{
    const RST        = "\033[0m";   // reset color
    const ERROR      = "\033[30;41m";
    const WARNING    = "\033[31;43m";
    const NOTICE     = "\033[1;30;43m";
    const PARSE      = "\033[45m";
    const DEPRECATED = "\033[30;47m";
    const CYAN       = "\033[0;36m";
    const GRAY       = "\033[0;37m";
    const MAGENTA    = "\033[1;35m";

    protected $codeColor;

    public function __construct(ErrorObject $obj, SettingsInterface $settings)
    {
        $this->codeColor = [
            E_ERROR             => static::ERROR,
            E_CORE_ERROR        => static::ERROR,
            E_COMPILE_ERROR     => static::ERROR,
            E_USER_ERROR        => static::ERROR,
            E_RECOVERABLE_ERROR => static::ERROR,

            E_WARNING         => static::WARNING,
            E_CORE_WARNING    => static::WARNING,
            E_COMPILE_WARNING => static::WARNING,
            E_USER_WARNING    => static::WARNING,

            E_PARSE => static::PARSE,

            E_NOTICE      => static::NOTICE,
            E_USER_NOTICE => static::NOTICE,

            E_STRICT          => static::DEPRECATED,
            E_DEPRECATED      => static::DEPRECATED,
            E_USER_DEPRECATED => static::DEPRECATED,
        ];

        parent::__construct($obj, $settings);
    }

    protected function prepare(): string
    {
        $code    = $this->obj->getCode();
        $eName   = $this->obj->getName();
        $file    = $this->obj->getFile();
        $line    = $this->obj->getLine();
        $message = $this->obj->getMessage();

        $notice = $this->codeColor[$code]."[$code] $eName ".static::RST.static::CYAN." $file::$line ".static::RST."\n"
            .static::GRAY.$message.static::RST."\n";

        if ($this->settings->get('handleTrace')) {
            $notice .= ((new CliTraceHandler($this->obj->getTrace(), $this->settings))->getTrace());
        }
        return $notice;
    }

    protected function notify(string $notice)
    {
        echo "\n".$notice;
    }

}