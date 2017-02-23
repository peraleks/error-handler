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
        $viewDir = dirname(__DIR__).'/View';
        $this->errorCss   = $viewDir.'/error.css';
        $this->traceCss   = $viewDir.'/trace.css';
        $this->errorTpl   = $viewDir.'/error.tpl.php';
        $this->wrapperTpl = $viewDir.'/wrapper.tpl.php';
    }

    public function notify()
    {
        $sets = $this->settingsObject;

        if (!$sets->get('deferredView')) {
            $this->view();
            return;
        }
        $this->errorHandler->addToCallbackDataArray('htmlNotifier', $this);
        if (!self::$count) {
            $this->errorHandler->addCallback(function ($callbackData) use ($sets){
                $sets->setNotifierClass(__CLASS__);
                $hideView = $sets->get('hideView') ? 'hidden' : '';
                include($this->wrapperTpl);
            });
            ++self::$count;
        }
    }

    public function view()
    {
        $eObj = $this->errorObject;
        $sets = $this->settingsObject;

        $code    = $eObj->getCode();
        $name    = $eObj->getName();
        $type    = $eObj->getType();
        $message = $eObj->getMessage();
        $path    = $sets->appDir();
        $file    = preg_replace('#^'.$path.'#', '', $eObj->getFile());
        $line    = $eObj->getLine();
        $style   = file_get_contents($this->errorCss);

        /* получаем трейс для конкретной ошибки если указана битовая маска в файле настроек*/
        if (0 != ($sets->get('handleTraceFor') & $eObj->getCode())) {
            $trace = (new HtmlTraceHandler($eObj->getTrace(), $sets))->getTrace();
            $style .= file_get_contents($this->traceCss);
        } else { $trace = ''; }

        $sets->get('hideTrace') ? $hidden = 'hidden' : $hidden = '';
        $fontSize = $sets->get('fontSize');
        $code == E_ERROR ? $cssName = 'ERROR' : $cssName = $name;
        $handler = $eObj->getHandler();

        include ($this->errorTpl);

    }
}