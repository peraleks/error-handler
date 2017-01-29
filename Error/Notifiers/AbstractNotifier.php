<?php

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\Core\SettingsObject;
use MicroMir\Error\Core\ErrorObject;

abstract class AbstractNotifier
{
    protected $obj;

    protected $settings;

    public function __construct(ErrorObject $obj, SettingsObject $settings)
    {
        $this->obj = $obj;
        $this->settings = $settings;
        $this->display();
    }

    abstract protected function display();

}