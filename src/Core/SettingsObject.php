<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;


use Peraleks\ErrorHandler\Exception\ErrorHandlerException;

class SettingsObject implements SettingsInterface
{
    private $settings = [
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
            throw new ErrorHandlerException('ErrorHandler::instance($file): $file must be a string, '.gettype($file).' defined');
        } elseif (!file_exists($file)) {
            throw new ErrorHandlerException('File not exist: ErrorHandler::instance('.$file.')');
        } elseif (!is_array($arr = include $file)) {
            throw new ErrorHandlerException('The configuration file '.$file.' should return an array, '.gettype($arr).' returned');
        }
        $this->settings = array_merge($this->settings, $arr);
        $this->settingsValidate();
    }

    private function settingsValidate()
    {
        // валидация режима работы
        if (PHP_SAPI === 'cli') $this->mode = 'CLI';
        else $this->productionMode() ?: $this->mode = 'DEV';

        // валидация настройки APP_DIR
        $ad =& $this->settings['APP_DIR'];
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
        return $this->settings[$this->mode];
    }

    public function productionMode(): bool
    {
        if (defined($this->settings['DEVELOPMENT_MODE_CONSTANT'])
            && constant($this->settings['DEVELOPMENT_MODE_CONSTANT']) === true
        ) { return false; } else { return true; }
    }

    public function getErrorReporting(): int
    {
        return $this->settings['ERROR_REPORTING'];
    }

    public function get(string $param)
    {
        //TODO обработка ошибки нестрокового типа аргумента $param
        if (!isset($this->settings[$this->mode][$this->currentNotifier][$param]))
            return null;
        return $this->settings[$this->mode][$this->currentNotifier][$param];
    }

    public function appDir(): string
    {
        //TODO оповещение об ощибочных настройках
        if (is_string($this->settings['APP_DIR'])) {
            return $this->settings['APP_DIR'];
        }
        return ' ';
    }
}