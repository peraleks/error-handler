<?php

namespace MicroMir\Error;

class Settings
{
    private $settings = ['APP_DIR' => ' ', 'DEV' => [], 'PROD' => []];

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
        !$this->devMode() ?: $this->mode = 'DEV';
    }

    public function setNotifierClass($notifierClass)
    {
        $this->currentNotifier = $notifierClass;
        \d::d($this);
    }

    public function getNotifiers()
    {
        return $this->settings[$this->mode];
    }

    public function devMode()
    {
        return true;
        //TODO определение режима разработчика
    }

    public function get(string $param)
    {
        //TODO обработка ошибки нестрокового типа аргумента $param
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