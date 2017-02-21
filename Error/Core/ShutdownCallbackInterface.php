<?php
namespace MicroMir\Error\Core;

interface ShutdownCallbackInterface
{
    public function addToCallbackDataArray(string $key, $value);

    public function addCallback(callable $callback);
}