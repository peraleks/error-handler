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

interface FormatterInterface
{
    function getFormattedTrace(array $dBTrace, ConfigObject $configObject): string;
}