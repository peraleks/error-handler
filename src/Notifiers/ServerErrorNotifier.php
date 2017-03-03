<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;


class ServerErrorNotifier extends AbstractNotifier
{
    protected $includeFile;

    protected $header = 'HTTP/1.1 500 Internal Server Error';

    protected function prepare()
    {
        !is_string($header = $this->configObject->get('header')) ?: $this->header = $header;
        $this->includeFile = $this->validateTemplate($this->configObject->get('includeFile'));
    }

    protected function validateTemplate($file): string
    {
        $default = dirname(__DIR__).'/View/serverError500.php';

        if ('' === $file || !is_string($file)) {
            return $default;
        }
        if (!file_exists($file)) {
            trigger_error('ServerErrorNotifier: file '.$file.' not exist', E_USER_WARNING);
            return $default;
        }
        return $file;
    }

    public function notify()
    {
        headers_sent() ?: header($this->header);
        echo $this->finalStringError;
        return true;
    }

    protected function ErrorToString(string $trace): string
    {
        ob_start();
        include $this->includeFile;
        return ob_get_clean();
    }
}