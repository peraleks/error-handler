<?php

namespace MicroMir\Error\Core;


use MicroMir\Error\ErrorObjects\AbstractErrorObject;

class Helper
{
    private $settings;

    public function __construct(SettingsObject $settings)
    {
        $this->settings = $settings;
    }

    public function handle(AbstractErrorObject $obj)
    {
        \d::d($obj);
        $code = $obj->getCode();
        if ($code !== ($code & $this->settings->get('ERROR_REPORTING'))) return false;

        $this->notify($obj);
        return true;
    }

    private function notify($obj)
    {
//        \d::d($this->settings);
        foreach ($this->settings->getNotifiers() as $notifierClass => ${0}) {
            $this->settings->setNotifierClass($notifierClass);
            new $notifierClass($obj, $this->settings);
        }
    }
}