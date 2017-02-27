<?php
namespace Peraleks\ErrorHandler\Core;

interface ShutdownCallbackInterface
{
    public function addErrorCallbackData(string $key, $value);

    public function addErrorCallback(callable $callback);
}
