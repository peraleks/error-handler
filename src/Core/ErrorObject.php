<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

final class ErrorObject
{
    protected $throwable;

    protected $code;

    protected $type = '';

    protected $trace;

    protected $handler = '';

    public static $codeName = [
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
        E_STRICT            => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED        => 'DEPRECATED',
        E_USER_DEPRECATED   => 'USER_DEPRECATED',
    ];

    /* @var $throwable \Throwable */
    public function __construct($throwable, string $handler)
    {
        $this->handler = $handler;
        $this->throwable = $throwable;
        $this->code = $this->throwable->getCode();
        if ($this->throwable instanceof \ErrorException) {
            $this->type = self::$codeName[$this->code] ?? "unknown";
        } else {
            $this->throwable instanceof \ParseError ? $this->code = E_PARSE : $this->code = E_ERROR;
            $this->type = get_class($this->throwable);
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->throwable->getMessage();
    }

    public function getFile(): string
    {
        return str_replace('\\', '/', $this->throwable->getFile());
    }

    public function getLine(): int
    {
        return $this->throwable->getLine();
    }

    public function getTrace(): array
    {
        if ($this->trace) {
            return $this->trace;
        } elseif ($this->throwable instanceof \ErrorException) {
            $this->trace = $this->throwable->getTrace();
            array_shift($this->trace);
            return $this->trace;
        } else {
            return $this->throwable->getTrace();
        }
    }

    public function getTraceAsString(): string
    {
        return $this->throwable->getTraceAsString();
    }

    public function getPrevious(): \Throwable
    {
        return $this->throwable->getPrevious();
    }

    public function __toString(): string
    {
        return (string)$this->throwable;
    }
}
