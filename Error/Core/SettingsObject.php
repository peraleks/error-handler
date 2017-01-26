<?php

namespace MicroMir\Error\Core;

class SettingsObject
{
    private $settings = [
        'ERROR_REPORTING' => E_ALL,
        'APP_DIR'     => ' ',
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
            $this->settingsError = 'Error in the name of the settings file';
            //TODO обработка ошибки подключения файла
        } else {
            try { $this->settings = array_merge($this->settings, include $file);
            } catch (\Error $e) {
                \d::d($e);
                //TODO обработка ошибки подключения файла
            }
        }
        $this->productionMode() ?: $this->mode = 'DEV';
    }

    public function setNotifierClass($notifierClass)
    {
        $this->currentNotifier = $notifierClass;
    }

    public function getNotifiers()
    {
        if (PHP_SAPI === 'cli') return $this->settings['CLI'];
        return $this->settings[$this->mode];
    }

    public function productionMode()
    {
        return false;
        //TODO определение режима разработчика
    }

    public function get(string $param)
    {
        //TODO обработка ошибки нестрокового типа аргумента $param
        if ($param == 'ERROR_REPORTING') return $this->settings['ERROR_REPORTING'];
        if (!isset($this->settings[$this->mode][$this->currentNotifier][$param])) return null;
        return $this->settings[$this->mode][$this->currentNotifier][$param];
    }

    public function appDir()
    {
        if (is_string($this->settings['APP_DIR'])) {
            return $this->settings['APP_DIR'];
        }
        return ' ';
    }
}