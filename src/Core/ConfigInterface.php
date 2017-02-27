<?php
namespace Peraleks\ErrorHandler\Core;

interface ConfigInterface
{
    public function setNotifierClass(string $notifierClass);

    public function getNotifiers(): array;

    public function productionMode(): bool;

    public function getErrorReporting(): int;

    public function get(string $param);

    public function appDir(): string ;
}
