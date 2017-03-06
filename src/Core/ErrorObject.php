<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

final class ErrorObject
{
    protected $e;

    protected $code;

    protected $type = '';

    protected $trace;

    protected $handler = '';

    protected  $codeName = [
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

    public function __construct(\Throwable $e, string $handler)
    {
        $this->handler = $handler;
        $this->e = $e;
        $this->code = $this->e->getCode();
        if ($this->e instanceof \ErrorException) {
            $this->type = $this->codeName[$this->code] ?? "unknown";
        } else {
            $this->e instanceof \ParseError ? $this->code = E_PARSE : $this->code = E_ERROR;
            $this->type = get_class($this->e);
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
        return $this->e->getMessage();
    }

    public function getFile(): string
    {
        return str_replace('\\', '/', $this->e->getFile());
    }

    public function getLine(): int
    {
        return $this->e->getLine();
    }

    public function getTrace(): array
    {
        if ($this->trace) {
            return $this->trace;
        } elseif ($this->e instanceof \ErrorException) {
            $this->trace = $this->e->getTrace();
            array_shift($this->trace);
            return $this->trace;
        } else {
            return $this->e->getTrace();
        }
    }

    public function getTraceAsString(): string
    {
        return $this->e->getTraceAsString();
    }

    public function getPrevious(): \Throwable
    {
        return $this->e->getPrevious();
    }

    public function __toString(): string
    {
        return (string)$this->e;
    }
}
