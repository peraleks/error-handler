<?php

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\Trace\HtmlTraceHandler;


class HtmlNotifier extends AbstractNotifier
{
    protected function display()
    {
        $code    = $this->obj->getCode();
        $name    = $this->obj->getName();
        $message = $this->obj->getMessage();
        $path    = $this->settings->appDir();
        $file    = str_replace($path.'/', '', $this->obj->getFile());
        $line    = $this->obj->getLine();
        $style   = file_get_contents(dirname(__DIR__).'/View/error.css');
        if ($this->settings->get('handleTrace')) {
            $trace = (new HtmlTraceHandler($this->obj->getTrace(), $this->settings))->result();
            $style .= file_get_contents(dirname(__DIR__).'/View/trace.css');
        } else {
            $trace = '';
        }
        $this->settings->get('minimizeTrace') ? $hidden = 'hidden' : $hidden = '';
        $fontSize = $this->settings->get('fontSize');

        include (dirname(__DIR__).'/View/error.tpl.php');
    }
}