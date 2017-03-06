<?php

namespace Peraleks\ErrorHandler\Core;

interface ShutdownCallbackInterface
{
    /**
     * Сохраняет данные ошибок в двумерный массив [$key][0 => $value]
     *
     * Данные будут переданы в callback в shutdown function.
     * Ключи $key лучше задавать по названию класса уведомителя,
     * используя переменную __CLASS__
     *
     * @param string $key
     * @param $value  mixed данные ошибок
     * @return void
     */
    public function addErrorCallbackData(string $key, $value);

    /**
     * Регистрирует callback функцию для отложенной обработки ошибок.
     *
     * Функция будет вызвана в shutdown function. Аргументом будет передан
     * массив данных, сохранённых при помощи addErrorCallbackData()
     *
     * @param callable $callback функция для обработки и вывода ошибок
     * @return void
     */
    public function addErrorCallback(callable $callback);
}
