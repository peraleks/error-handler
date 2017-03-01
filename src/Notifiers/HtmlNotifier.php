<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;

use Peraleks\ErrorHandler\Trace\HtmlTraceHandler;

class HtmlNotifier extends AbstractNotifier
{
    protected $errorCss;

    protected $traceCss;

    protected $errorTpl;

    protected $wrapperTpl;

    protected static $count;

    protected function prepare()
    {
        $dir = dirname(__DIR__).'/View';
        $this->errorCss   = $dir.'/error.css';
        $this->traceCss   = $dir.'/trace.css';
        $this->errorTpl   = $dir.'/error.tpl.php';
        $this->wrapperTpl = $dir.'/wrapper.tpl.php';
        $this->traceHandlerClass = HtmlTraceHandler::class;
    }

    public function notify()
    {
        $conf = $this->configObject;

        if (!$conf->get('deferredView')) {
            echo $this->renderedError;
            return;
        }
        $this->errorHandler->addErrorCallbackData(__CLASS__, $this->renderedError);
        if (!static::$count) {
            $this->errorHandler->addErrorCallback(function ($callbackData) use ($conf) {
                $conf->setNotifierClass(__CLASS__);
                $hideView = $conf->get('hideView') ? 'hidden' : '';
                $errors = $callbackData[__CLASS__];
                $count = count($errors);
                include($this->wrapperTpl);
            });
            ++static::$count;
        }
    }

    protected function ErrorToString(string $trace): string
    {
        $eObj = $this->errorObject;
        $conf = $this->configObject;

        $code     = $eObj->getCode();
        $type     = $eObj->getType();
        $message  = $eObj->getMessage();
        $path     = $conf->appDir();
        $file     = preg_replace('#^'.$path.'#', '', $eObj->getFile());
        $line     = $eObj->getLine();
        $fontSize = $conf->get('fontSize');
        $handler  = $eObj->getHandler();
        $code == E_ERROR ? $cssType = 'ERROR' : $cssType = $type;
        $conf->get('hideTrace') ? $hidden = 'hidden' : $hidden = '';
        $style    = file_get_contents($this->errorCss);
        $trace == '' ?: $style .= file_get_contents($this->traceCss);

        ob_start();
        include($this->errorTpl);
        return ob_get_clean();
    }
}
