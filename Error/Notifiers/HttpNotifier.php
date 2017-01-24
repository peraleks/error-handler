<?php

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\Trace\HttpTraceHandler;


class HttpNotifier extends AbstractNotifier
{
    protected function display()
    {
        $code    = $this->obj->getCode();
        $name    = $this->obj->getName();
        $message = $this->obj->getMessage();
        $file    = $this->obj->getFile();
        $line    = $this->obj->getLine();
        $style   = file_get_contents(dirname(__DIR__).'/View/error.css');
        if ($this->settings->get('trace')) {
            $trace = (new HttpTraceHandler($this->obj->getTrace()))->result();
            $style .= file_get_contents(dirname(__DIR__).'/View/trace.css');
        } else {
            $trace = '';
        }
        $this->settings->get('hidden') ? $hidden = 'hidden' : $hidden = '';
        \d::d($this->settings->get('hidden'));

        include (dirname(__DIR__).'/View/error.tpl.php');
    }
}