<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;


use Peraleks\ErrorHandler\Trace\BrowserConsoleTraceHandler;

class BrowserConsoleNotifier extends AbstractNotifier
{
    const ERROR      = "#e02828";
    const WARNING    = "#ffaa00";
    const NOTICE     = "#d8d800";
    const PARSE      = "#ba59bf";
    const DEPRECATED = "#c48c00";

    const SCRIPT = '<script>%s</script>';

    const HEADER = "console.%s('%s %s',"
                    ."'background: %s;"
                    ."color: #fff;"
                    ."padding: 0.3em 0.7em 0.3em 0.2em;"
                    ."line-height: 1.5em;"
                    ."border-radius: 1em');";

    const MESSAGE = "console.%s('%s');";

    const FILE = "console.%s('%s %s', 'color: #00aaaa; padding-left: 1em');";

    const END = "console.%s('%s %s',"
                    ."'background: %s;"
                    ."color: #fff;"
                    ."padding: 0.2em 0.5em 0.2em 0;"
                    ."line-height: 1.2em;"
                    ."border-radius: 1em');";

    protected $codeColor;

    protected static $count;

    protected $console = 'log';

    protected function prepare()
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


        if ($v = $this->configObject->get('console')) {
            !preg_match('/^error$|^warn$|^info$|^log$|^debug$/', $v, $matches)
                ?: $this->console = $matches[0];
        }
    }

    protected function getTraceHandlerClass(): string
    {
        return BrowserConsoleTraceHandler::class;
    }


    public function notify()
    {
        $conf = $this->configObject;

        if (!$conf->get('deferredView')) {
            echo $this->finalStringError;
            return;
        }
        $this->errorHandler->addErrorCallbackData(__CLASS__, $this->finalStringError);
        if (!static::$count) {
            $this->errorHandler->addErrorCallback(function ($callbackData) {
                $errors = $callbackData[__CLASS__];
                foreach ($errors as $error) {
                    echo $error;
                }
            });
            ++static::$count;
        }
    }

    protected function ErrorToString(string $trace): string
    {
        $eObj  = $this->errorObject;
        $color =& $this->codeColor;
        $cons  =& $this->console;

        $code     = $eObj->getCode();
        $type     = $eObj->getType();
        $message  = $eObj->getMessage();
        $file     = $eObj->getFile().' ( '.$eObj->getLine().' )';

        if ('' != $trace && $this->configObject->get('phpNativeTrace'))  {
            $trace = addslashes($trace);
            $trace = preg_replace("/\n/", '\n', $trace);
        }

        $string = sprintf(static::HEADER, $cons, '%c', $type.' ['.$code.']', $color[$code]);

        $string .= sprintf(static::MESSAGE, $cons, addslashes($message));

        $string .= sprintf(static::FILE, $cons, '%c', $file, $color[$code]);

        '' == $trace ?: $string .= sprintf(static::MESSAGE, $cons, $trace);

        $string .= sprintf(static::END, $cons, '%c', '^', $color[$code]);

        return sprintf(static::SCRIPT, $string);
    }
}