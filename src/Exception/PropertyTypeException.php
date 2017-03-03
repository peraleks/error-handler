<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Exception;

class PropertyTypeException extends ErrorHandlerException
{
    use ExceptionSourceNameTrait;

    public function __construct($value, string $property, string $type)
    {
        $this->message
            = $this->exceptionSourceName().': the property value \''.$property.'\'=> must be a '.$type
        .', '.gettype($value).' defined';
    }
}
