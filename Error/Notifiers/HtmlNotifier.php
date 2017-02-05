<?php
declare(strict_types=1);

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\Trace\HtmlTraceHandler;


class HtmlNotifier extends AbstractNotifier
{
    protected function prepare(): string { return ''; }

    protected function notify(string $notice)
    {
        $code    = $this->obj->getCode();
        $name    = $this->obj->getName();
        $type    = $this->obj->getType();
        $message = $this->obj->getMessage();
        $path    = $this->settings->appDir();
        $file    = str_replace($path.'/', '', $this->obj->getFile());
        $line    = $this->obj->getLine();
        $style   = file_get_contents(dirname(__DIR__).'/View/error.css');
        if ($this->settings->get('handleTrace')) {
            $trace = (new HtmlTraceHandler($this->obj->getTrace(), $this->settings))->getTrace();
            $style .= file_get_contents(dirname(__DIR__).'/View/trace.css');
        } else {
            $trace = '';
        }
        $this->settings->get('minimizeTrace') ? $hidden = 'hidden' : $hidden = '';
        $fontSize = $this->settings->get('fontSize');
        $code == E_ERROR ? $cssName = 'ERROR' : $cssName = $name;
        $handler = $this->obj->getHandler();

        include (dirname(__DIR__).'/View/error.tpl.php');
    }
}