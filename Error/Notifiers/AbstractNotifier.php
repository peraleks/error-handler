<?php

namespace MicroMir\Error\Notifiers;

use MicroMir\Error\ErrorObjects\AbstractErrorObject;

abstract class AbstractNotifier
{
    protected $obj;

    protected $hidden;

    protected $handleTrace;

    public function __construct(AbstractErrorObject $obj, bool $handleTrace = true, bool $hidden = true)
    {
        $this->obj = $obj;
        $this->handleTrace = $handleTrace;
        $this->hidden = $hidden;
        $this->display();
    }

    abstract protected function display();

}