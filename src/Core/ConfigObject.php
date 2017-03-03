<?php
declare(strict_types = 1);

namespace Peraleks\ErrorHandler\Core;

use Peraleks\ErrorHandler\Exception\ErrorHandlerException;

class ConfigObject implements ConfigInterface
{
    private $config = [
        'ERROR_REPORTING' => E_ALL,
        'NOTIFIERS'       => [],
        'APP_DIR'         => '',
    ];

    private $currentNotifier;

    public function __construct($file)
    {
        if (!is_string($file)) {
            throw new ErrorHandlerException(
                'ErrorHandler::instance($file): $file must be a string, '.gettype($file).' defined'
            );
        } elseif (!file_exists($file)) {
            throw new ErrorHandlerException(
                'Configuration file not exist: ErrorHandler::instance('.$file.')'
            );
        } elseif (!is_array($arr = include $file)) {
            throw new ErrorHandlerException(
                'The configuration file '.$file.' should return an array, '.gettype($arr).' returned'
            );
        }
        $this->config = array_merge($this->config, $arr);
        $this->appDirValidate($this->config['APP_DIR']);
    }

    private function appDirValidate(&$appDir)
    {
        if (is_string($appDir)) return;
        $appDir = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'] ?? ''));
    }

    public function setNotifierClass(string $notifierClass)
    {
        $this->currentNotifier = $notifierClass;
    }

    public function getNotifiers(): array
    {
        return $this->config['NOTIFIERS'];
    }

    public function getErrorReporting(): int
    {
        return $this->config['ERROR_REPORTING'];
    }

    public function get(string $param)
    {
        return $this->config['NOTIFIERS'][$this->currentNotifier][$param] ?? null;
    }

    public function appDir(): string
    {
        return $this->config['APP_DIR'];
    }
}
