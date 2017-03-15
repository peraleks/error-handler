<?php
/**
 * PHP error handler and debugger.
 *
 * @package   Peraleks\ErrorHandler
 * @copyright 2017 Aleksey Perevoshchikov <aleksey.perevoshchikov.n@gmail.com>
 * @license   https://github.com/peraleks/error-handler/blob/master/LICENSE.md MIT
 * @link      https://github.com/peraleks/error-handler
 */

namespace Peraleks\ErrorHandler\Trace;


use Peraleks\ErrorHandler\Core\ConfigObject;

/**
 * Interface FormatterInterface
 *
 * Все форматировщики стека вызовов должны реализовывать
 * данный интерфейс.<br>
 * Так же форматировщик может расширять AbstractTraceFormatter,
 * который реализует данный интерфейс и выполняет часть работы
 * по форматированию стека.
 */
interface FormatterInterface
{
    /**
     * Возвращает форматированный стек вызовов.
     *
     * @param array        $dBTrace      стек вызовов
     * @param ConfigObject $configObject объект конфигурации
     * @return string  форматированный стек вызовов
     */
    function getFormattedTrace(array $dBTrace, ConfigObject $configObject): string;
}