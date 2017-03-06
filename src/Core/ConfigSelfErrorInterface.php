<?php


namespace Peraleks\ErrorHandler\Core;


interface ConfigSelfErrorInterface
{
    public function getMode(): string;

    public function getSelfLogFile(): string;
}