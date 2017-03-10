<?php
/**
 * PHP error handler and debugger.
 *
 * @package   Peraleks\ErrorHandler
 * @copyright 2017 Aleksey Perevoshchikov <aleksey.perevoshchikov.n@gmail.com>
 * @license   https://github.com/peraleks/error-handler/blob/master/LICENSE.md MIT
 * @link      https://github.com/peraleks/error-handler
 */

namespace Peraleks\ErrorHandler\Autoload;

/**
 * Class ClassLoader.
 *
 * Автозагрузчик классов для Peraleks\ErrorHandler.
 */
class ClassLoader
{
    /**
     * Корневая директория пакета.
     *
     * @var string
     */
    private $baseDir;

    /**
     * ClassLoader constructor.
     *
     * Регистрация автозагрузчика,
     * определение корневой директории пакета.
     */
    public function __construct()
    {
        spl_autoload_register(array($this, 'loader'), false, true);
        $this->baseDir = dirname(__DIR__);
    }

    /**
     * Подключает требуемый класс.
     *
     * @param  string $className полное имя класса
     * @return void
     */
    private function loader($className)
    {
        if (1 !== preg_match('/^Peraleks\\\ErrorHandler.*$/', $className)) return;
        include
            $this->baseDir.str_replace('\\', '/', str_replace('Peraleks\ErrorHandler', '', $className)).'.php';
    }
}
