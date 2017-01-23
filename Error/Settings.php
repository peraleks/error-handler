<?php


namespace MicroMir\Error;


class Settings
{
    public $user;

    public $errorSettings;

    const  DEFAULT = [
        'display'   => false,
        'log'       => false,
        'logFile'   => '',
        'trace'     => false,
        'strLength' => 80,
        'hideTrace' => true,
    ];

    const GUEST_MESSAGE = [
        'header'  => '500 Internal Server Error',
        'message' => [
            'Сервер отдыхает. Зайдите позже.',
            "Don't worry! Chip 'n Dale Rescue Rangers",
        ]
    ];

    public function __construct($file)
    {
        if (!is_string($file) || !file_exists($file) || !is_array($array = include $file)) {
            $this->errorSettings = 'Error settings file';
            $this->user = self::DEFAULT;
        } else {
            $this->user = array_merge(self::DEFAULT, $array);
        }
    }
}