<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;

use Peraleks\ErrorHandler\Core\ErrorObject;
use Peraleks\ErrorHandler\Core\SettingsInterface;
use Peraleks\ErrorHandler\Trace\CliTraceHandler;

class CliNotifier extends AbstractNotifier
{
    const ERROR      = "\033[30;41m%s\033[0m";
    const WARNING    = "\033[31;43m%s\033[0m";
    const NOTICE     = "\033[1;30;43m%s\033[0m";
    const PARSE      = "\033[45m%s\033[0m";
    const DEPRECATED = "\033[30;47m%s\033[0m";
    const FILE       = "\033[0;36m%s\033[0m";
    const MESSAGE    = "\033[37m%s\033[0m";

    protected $codeColor;

    protected $preparedNotice;

    public function __construct(ErrorObject $errorObject, SettingsInterface $settingsObject, $eH)
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

        parent::__construct($errorObject, $settingsObject, $eH);
    }

    protected function prepare()
    {
        $eObj = $this->errorObject;
        $sets = $this->settingsObject;

        $code    = $eObj->getCode();
        $eName   = $eObj->getName();
        $file    = $eObj->getFile();
        $line    = $eObj->getLine();
        $message = $eObj->getMessage();

        $notice = sprintf($this->codeColor[$code], "[$code] $eName ")
            .sprintf(static::FILE, " $file($line) ")."\n"
            .sprintf(static::MESSAGE, $message)."\n";

        if ($sets->get('handleTrace')) {
            $notice .= ((new CliTraceHandler($eObj->getTrace(), $sets))->getTrace());
        } else { $notice .= "\n"; }
        $this->preparedNotice = $notice;
    }

    public function notify()
    {
        echo "\n".$this->preparedNotice;
    }

}