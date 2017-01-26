<?php

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\Core\SettingsObject;
use MicroMir\Error\ErrorObjects\AbstractErrorObject;

abstract class AbstractNotifier
{
    protected $obj;

    protected $settings;

    public function __construct(AbstractErrorObject $obj, SettingsObject $settings)
    {
        $this->obj = $obj;
        $this->settings = $settings;
        $this->display();
    }

    abstract protected function display();

}