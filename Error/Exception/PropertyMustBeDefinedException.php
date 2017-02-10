<?php
declare(strict_types=1);

namespace MicroMir\Error\Exception;


class PropertyMustBeDefinedException extends ErrorHandlerException
{
    use ExceptionSourceNameTrait;

    public function __construct(string $property)
    {
        $this->message = $this->exceptionSourceName().': the property \''.$property.'\'=> must be defined';
    }
}