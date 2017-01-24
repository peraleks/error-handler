<?php

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\ErrorObjects\AbstractErrorObject;
use MicroMir\Error\Settings;

abstract class AbstractNotifier
{
    protected $obj;

    protected $settings;

    public function __construct(AbstractErrorObject $obj, Settings $settings)
    {
        $this->obj = $obj;
        $this->settings = $settings;
        $this->display();
    }

    abstract protected function display();

}