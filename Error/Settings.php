<?php


namespace MicroMir\Error;


class Settings
{
    public $s;

    const  DEFAULT = [
        'display'   => false,
        'log'       => false,
        'logFile'   => '',
        'trace'     => false,
        'strLength' => 80,
        'hideTrace' => true,
    ];

    const GUEST_MESSAGE = [
        'header' => '500 Internal Server Error',
        'message' => [
            0 => 'Сервер отдыхает. Зайдите позже.',
            1 => "Don't worry! Chip 'n Dale Rescue Rangers",
        ]
    ];

    public function __construct(array $array = null)
    {
        if (is_array($array)) {
            $this->s = array_merge(self::DEFAULT, $array);
        } else {
            $this->s = self::DEFAULT;
        }
    }
}