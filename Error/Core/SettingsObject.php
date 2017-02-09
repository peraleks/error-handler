<?php
declare(strict_types=1);

namespace MicroMir\Error\Core;


class SettingsObject implements SettingsInterface
{
    private $settings = [
        'ERROR_REPORTING' => E_ALL,
        'DEV'         => [],
        'PROD'        => [],
        'CLI'         => [],
    ];

    private $currentNotifier;

    private $mode = 'PROD';

    public $settingsError;

    public function __construct($file)
    {
        if (!is_string($file) || !file_exists($file)) {
            throw new \Exception('Wrong name of the settings file: '.$file);
        } elseif (!is_array($arr = include $file)) {
            throw new \Exception('The configuration file should return an array, '.gettype($file).' returned');
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
        return false;
        //TODO определение режима разработчика
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