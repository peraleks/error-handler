<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;


class ServerErrorNotifier extends AbstractNotifier
{
    protected $defaultIncludeFile;

    protected $includeFile;

    protected $header = 'HTTP/1.1 500 Internal Server Error';

    protected function prepare()
    {
        !is_string($header = $this->configObject->get('header')) ?: $this->header = $header;
        $this->defaultIncludeFile = dirname(__DIR__).'/View/serverError500.php';
        $this->includeFile = $this->validateIncludeFile($this->configObject->get('includeFile'));
    }

    protected function getTraceHandlerClass(): string
    {
        return '';
    }

    protected function validateIncludeFile($file): string
    {
        if ('' === $file || !is_string($file)) {
            return $this->defaultIncludeFile;
        }
        if (!file_exists($file)) {
            trigger_error('ServerErrorNotifier: file '.$file.' not exist', E_USER_WARNING);
            return $this->defaultIncludeFile;
        }
        return $file;
    }

    public function notify()
    {
        $this->clean();
        headers_sent() ?: header($this->header);
        echo $this->finalStringError;
        return true;
    }

    protected function clean()
    {
        ob_end_clean();
        if (0 < ob_get_level()) $this->clean();
    }

    protected function ErrorToString(string $trace): string
    {
        ob_start();
        try {
            include $this->includeFile;
        } catch (\Throwable $e) {
            trigger_error($e->getMessage().' in '.$e->getFile().':'.$e->getLine(), E_USER_WARNING);
            include $this->defaultIncludeFile;
            return ob_get_clean();
        }
        return ob_get_clean();
    }
}