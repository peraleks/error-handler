<?php

namespace MicroMir\Error;

use MicroMir\Error\Notifiers\HttpNotifier;

class Settings
{
    private $userSettings = [];

    private $currentNotifier;

    public $settingsError;

    public function __construct($file)
    {
        if (!is_string($file) || !file_exists($file) || !is_array($array = include $file)) {
            $this->settingsError = 'Error settings file';
        } else {
            $this->userSettings = $array;
        }
    }

    public function getNotifiers($devMode)
    {
        return [
          HttpNotifier::class => [],
        ];
    }

    public function devMode()
    {
        return true;
    }

    public function get(string $param)
    {
        if (!isset($this->userSettings[$this->currentNotifier][$param])) return null;
        return $this->userSettings[$this->currentNotifier][$param];
    }

    public function setNotifierClass($notifierClass)
    {
        $this->currentNotifier = $notifierClass;
    }
}