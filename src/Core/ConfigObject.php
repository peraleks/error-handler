<?php
declare(strict_types = 1);

namespace Peraleks\ErrorHandler\Core;

use Peraleks\ErrorHandler\Exception\ErrorHandlerException;

class ConfigObject implements ConfigInterface, ConfigSelfErrorInterface
{
    private $config = [
        'SELF_LOG_FILE'   => '',
        'ERROR_REPORTING' => E_ALL,
        'NOTIFIERS'       => [],
        'APP_DIR'         => '',
        'MODE'            => 'prod',
    ];

    private $currentNotifier;

    public function __construct($file)
    {
        if (!is_string($file)) {
            throw new ErrorHandlerException(
                'ErrorHandler::instance($file): $file must be a string, '.gettype($file).' given'
            );
        } elseif (!file_exists($file)) {
            throw new ErrorHandlerException(
                'Configuration file not exist: ErrorHandler::instance('.$file.')'
            );
        } elseif (!is_array($arr = include $file)) {
            throw new ErrorHandlerException(
                'The configuration file '.$file.' should return an array, '.gettype($arr).' given'
            );
        }
        $this->config = array_merge($this->config, $arr);
        $this->validateSelfLogFile($this->config['SELF_LOG_FILE']);
        $this->errorReportingValidate($this->config['ERROR_REPORTING']);
        $this->notifiersValidate($this->config['NOTIFIERS']);
        $this->appDirValidate($this->config['APP_DIR']);
        $this->modeValidate($this->config['MODE']);
    }

    private function validateSelfLogFile(&$selfLogFile)
    {
        is_string($selfLogFile) ?: $selfLogFile = '';
    }

    private function errorReportingValidate(&$eReporting)
    {
        is_int($eReporting) ?: $eReporting = E_ALL;
    }

    private function notifiersValidate(&$notifiers)
    {
        if (!is_array($notifiers)) {
            $type = gettype($notifiers);
            $notifiers = [];
            trigger_error(
                'Configuration file: value of key \'NOTIFIERS\' must be an array, '.$type.' given',
                E_USER_ERROR
            );
        }
    }

    private function appDirValidate(&$appDir)
    {
        if (!is_string($appDir) || 'default' === $appDir) {
            $appDir = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'] ?? ''));
        } else {
            $appDir = str_replace('\\', '/', $appDir);
        }
    }

    private function modeValidate(&$mode)
    {
        if ('dev' !== $mode) $mode = 'prod';
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

    public function getAppDir(): string
    {
        return $this->config['APP_DIR'];
    }

    public function getMode(): string
    {
        return $this->config['MODE'];
    }
    
    public function getSelfLogFile(): string
    {
        return $this->config['SELF_LOG_FILE'];
    }
}
