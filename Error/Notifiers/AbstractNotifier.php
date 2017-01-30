<?php
declare(strict_types=1);

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\Core\SettingsInterface;
use MicroMir\Error\Core\ErrorObject;

abstract class AbstractNotifier
{
    protected $obj;

    protected $settings;

    public function __construct(ErrorObject $obj, SettingsInterface $settings)
    {
        $this->obj = $obj;
        $this->settings = $settings;
        $this->display();
    }

    abstract protected function display();

}