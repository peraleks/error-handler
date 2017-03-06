<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;


class SelfErrorHandler
{
    private  $codeName = [
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

    private $selfLogFile;

    private $devMode;

    public function __construct(ConfigSelfErrorInterface $configObject = null)
    {
        if ($configObject && ('' !== $configObject->getSelfLogFile())) {
            $this->selfLogFile = $configObject->getSelfLogFile();
        } else {
            $this->selfLogFile = $_SERVER['DOCUMENT_ROOT'].'/error_handler_'
                .crc32($_SERVER['DOCUMENT_ROOT'].$_SERVER['SERVER_SOFTWARE']).'.log';
        }
        $this->devMode = $configObject && ('dev' == $configObject->getMode());
    }

    public function report(\Throwable $e)
    {
        if (PHP_SAPI === 'cli') {
            $this->cliReport($e);
            return;
        }
        if ($this->devMode) {
            $this->htmlReport($e);
            $this->fileReport($e, $this->selfLogFile);
        } else {
            $this->fileReport($e, $this->selfLogFile);
        }
    }

    private function cliReport($e)
    {
        echo "\033[32m".(string)$e."\033[0m";
    }

    private function htmlReport($e)
    {
        if ($e instanceof \ErrorException) {
            $type = $this->codeName[$e->getCode()] ?? 'unknown';
        } else {
            $type = get_class($e);
        }
        $file    = $e->getFile();
        $line    = $e->getLine();
        $message = $e->getMessage();
        $trace   = '<pre>'.$e->getTraceAsString().'</pre>';

        include dirname(__DIR__).'/View/selfError.tpl.php';
    }

    private function fileReport($e, string $file)
    {
        if ($fileResource = fopen($file, 'ab')) {
            fwrite($fileResource, "\n[".date('d-M-o H:i:s O').'] '.(string)$e."\n");
            fclose($fileResource);
        }
    }
}