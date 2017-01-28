<?php

namespace MicroMir\Error\ErrorObjects;

use MicroMir\Error\TraceHandler;

abstract class AbstractErrorObject
{
    protected $code;

    protected $name;

    protected $message;

    protected $replaces;

    protected $file;

    protected $line;

    protected $trace = [];

    private static $codeName = [
        E_ERROR             => 'ERROR',
        E_WARNING           => 'WARNING',
        E_PARSE             => 'PARSE',
        E_NOTICE            => 'NOTICE',
        E_CORE_ERROR        => 'CORE_ERROR',
        E_CORE_WARNING      => 'CORE_WARNING',
        E_COMPILE_ERROR     => 'COMPILE_ERROR',
        E_COMPILE_WARNING   => 'COMPILE_WARNING',
        E_USER_ERROR        => 'USER_ERROR',
        E_USER_WARNING      => 'USER_WARNING',
        E_USER_NOTICE       => 'USER_NOTICE',
        E_USER_DEPRECATED   => 'USER_DEPRECATED',
        E_STRICT            => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED        => 'DEPRECATED',
        3                   => 'UNCAUGHT_EXCEPTION',
    ];

    abstract public function __construct(array $dBTrace);

    protected static function getErrorName($code): string
    {
        if (isset(self::$codeName[$code])) return self::$codeName[$code];
        return "UNKNOWN";
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLine(): string
    {
        return $this->line;
    }

    public function getTrace(): array
    {
        return $this->trace;
    }
}