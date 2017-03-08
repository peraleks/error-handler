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

    private $code;

    private $traceEnabled = E_ERROR | E_RECOVERABLE_ERROR;

    private $error500 = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

    public function __construct(ConfigSelfErrorInterface $configObject = null)
    {
        if ($configObject && ('' !== $configObject->getSelfLogFile())) {
            $this->selfLogFile = $configObject->getSelfLogFile();
        } elseif (PHP_SAPI !== 'cli') {
            $this->selfLogFile = $_SERVER['DOCUMENT_ROOT'].'/error_handler_'
                .crc32($_SERVER['DOCUMENT_ROOT'].$_SERVER['SERVER_SOFTWARE']).'.log';
        }
        $this->devMode = $configObject && ('dev' === $configObject->getMode());
    }

    public function report($e)
    {

        if (!$e instanceof \ErrorException && !$e instanceof ErrorObject) {
            $this->code = $e instanceof \ParseError ? E_PARSE : E_ERROR;
        } else {
            $this->code = $e->getCode();
        }

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
        echo "\n\033[32m".$this->getStringError($e)."\033[0m\n";
    }

    private function htmlReport($e)
    {
        $type    = $this->getType($e);
        $file    = $e->getFile();
        $line    = $e->getLine();
        $message = $e->getMessage();
        $trace   = $this->code & $this->traceEnabled ? '<pre>'.$e->getTraceAsString().'</pre>' : '';

        include dirname(__DIR__).'/View/selfError.tpl.php';
    }

    private function fileReport($e, string $file)
    {
        if ($r = fopen($file, 'ab')) {
            fwrite($r, "\n[".date('d-M-o H:i:s O').'] '.$this->getStringError($e)."\n");
            fclose($r);
        }

        /* Если $e->getCode() вернёт 0 (\Throwable), значит ошибка сгенерирована
         * внутри обработчика и была перехвачена - поэтому скрипт не останавливаем
         * и не посылаем состояние 500.
         * Если $e является экземпляром ErrorObject, значит ошибка была в клиентской
         * части кода. ErrorObject конвертирует коды ошибок и никогда не возвращает 0.
         * В этом случае при совпадении кода с маской $this->error500 и в режиме production
         * посылаем состояние 500 и останавливаем скрипт.
         * Если фатальная ошибка произошла внутри обработчика, аналогично - 500 и exit */
        if (!$this->devMode && ($e->getCode() & $this->error500)) {
            $this->clean();
            headers_sent() ?: header('HTTP/1.1 500 Internal Server Error');
            include dirname(__DIR__).'/View/serverError500.php';
            exit;
        }
    }

    private function clean()
    {
        ob_end_clean();
        if (0 < ob_get_level()) $this->clean();
    }

    private function getType($e): string
    {
        if ($e instanceof \ErrorException) {
            return $this->codeName[$e->getCode()] ?? 'unknown';
        }
        return get_class($e);
    }

    private function getStringError($e): string
    {
        if (!($this->code & $this->traceEnabled)) {
            return $this->getType($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine();
        }
        return (string)$e;
    }

}