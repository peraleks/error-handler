<?php
declare(strict_types=1);

namespace MicroMir\Error\Core;

class Helper
{
    private $settings;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(ErrorObject $obj)
    {
        $code = $obj->getCode();
        if ($code !== ($code & $this->settings->get('ERROR_REPORTING'))) return;

        $this->notify($obj);
        if ($code == E_RECOVERABLE_ERROR) exit();
    }

    private function notify($obj)
    {
        foreach ($this->settings->getNotifiers() as $notifierClass => ${0}) {
            $this->settings->setNotifierClass($notifierClass);
            new $notifierClass($obj, $this->settings);
        }
    }
}