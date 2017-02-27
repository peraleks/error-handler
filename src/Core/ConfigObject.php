<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

use Peraleks\ErrorHandler\Exception\ErrorHandlerException;

class ConfigObject implements ConfigInterface
{
    private $config = [
        'ERROR_REPORTING' => E_ALL,
        'DEVELOPMENT_MODE_CONSTANT' => '',
        'DEV'         => [],
        'PROD'        => [],
        'CLI'         => [],
    ];

    private $currentNotifier;

    private $mode = 'PROD';

    public function __construct($file)
    {
        if (!is_string($file)) {
            throw new ErrorHandlerException(
                'ErrorHandler::instance($file): $file must be a string, '.gettype($file).' defined'
            );
        } elseif (!file_exists($file)) {
            throw new ErrorHandlerException(
                'File not exist: ErrorHandler::instance('.$file.')'
            );
        } elseif (!is_array($arr = include $file)) {
            throw new ErrorHandlerException(
                'The configuration file '.$file.' should return an array, '.gettype($arr).' returned'
            );
        }
        $this->config = array_merge($this->config, $arr);
        $this->setMode();
        $this->appDirValidate();
    }

    private function setMode()
    {
        if (PHP_SAPI === 'cli') {
            $this->mode = 'CLI';
        } else {
            $this->productionMode() ?: $this->mode = 'DEV';
        }
    }

    private function appDirValidate()
    {
        $ad =& $this->config['APP_DIR'];
        $ad = is_string($ad) ? $ad : null;
        $ad = $ad ?? dirname($_SERVER['DOCUMENT_ROOT'] ?? '');
        $ad = str_replace('\\', '/', $ad);
    }

    public function setNotifierClass(string $notifierClass)
    {
        $this->currentNotifier = $notifierClass;
    }

    public function getNotifiers(): array
    {
        return $this->config[$this->mode];
    }

    public function productionMode(): bool
    {
        if (defined($this->config['DEVELOPMENT_MODE_CONSTANT'])
            && true === constant($this->config['DEVELOPMENT_MODE_CONSTANT'])
        ) {
            return false;
        }
        return true;
    }

    public function getErrorReporting(): int
    {
        return $this->config['ERROR_REPORTING'];
    }

    public function get(string $param)
    {
        if (!isset($this->config[$this->mode][$this->currentNotifier][$param])) {
            return null;
        }
        return $this->config[$this->mode][$this->currentNotifier][$param];
    }

    public function appDir(): string
    {
        return $this->config['APP_DIR'];
    }
}
