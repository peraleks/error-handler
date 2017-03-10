<?php
/**
 * PHP error handler and debugger.
 *
 * @package   Peraleks\ErrorHandler
 * @copyright 2017 Aleksey Perevoshchikov <aleksey.perevoshchikov.n@gmail.com>
 * @license   https://github.com/peraleks/error-handler/blob/master/LICENSE.md MIT
 * @link      https://github.com/peraleks/error-handler
 */

declare(strict_types=1);

namespace Peraleks\ErrorHandler\Notifiers;

use Peraleks\ErrorHandler\Trace\HtmlTraceHandler;

/**
 * Class HtmlNotifier
 *
 * Форматирует и выводит ошибку в браузер ввиде HTML.
 */
class HtmlNotifier extends AbstractNotifier
{
    /**
     * Полное имя файла стилей для html-шаблона ошибки.
     *
     * @var string
     */
    protected $errorCss;

    /**
     * Полное имя файла стилей для стека вызовов.
     *
     * @var string
     */
    protected $traceCss;

    /**
     * Полное имя файла html-шаблона ошибки.
     *
     * @var string
     */
    protected $errorTpl;

    /**
     * Полное имя файла html-шаблона обёртки для отложенного показа ошибок.
     *
     * @var string
     */
    protected $wrapperTpl;

    /**
     * Счётчик callbacks для отложенного показа ошибок.
     * Используется для того, чтобы не регистрировать callback повторно.
     *
     * @var null | int
     */
    protected static $count;

    /**
     * Задаёт файлы шаблонов и css.
     *
     * @return void
     */
    protected function prepare()
    {
        $dir = dirname(__DIR__).'/View';
        $this->errorCss   = $dir.'/error.css';
        $this->traceCss   = $dir.'/trace.css';
        $this->errorTpl   = $dir.'/error.tpl.php';
        $this->wrapperTpl = $dir.'/wrapper.tpl.php';
    }

    /**
     * Возвращает имя класса обработчика стека вызовов.
     *
     * @return string HtmlTraceHandler::class
     */
    protected function getTraceHandlerClass(): string
    {
        return HtmlTraceHandler::class;
    }

    /**
     * Форматирует ошибку в HTML.
     *
     * @param string $trace стек вызовов
     * @return string ошибка в формате HTML
     */
    protected function ErrorToString(string $trace): string
    {
        $eObj = $this->errorObject;
        $conf = $this->configObject;

        $code     = $eObj->getCode();
        $type     = $eObj->getType();
        $message  = $eObj->getMessage();
        $path     = $conf->getAppDir();
        $file     = preg_replace('#^'.$path.'#', '', $eObj->getFile());
        $line     = $eObj->getLine();
        $fontSize = $conf->get('fontSize');
        $handler  = $eObj->getHandler();
        $code == E_ERROR ? $cssType = 'ERROR' : $cssType = $type;
        $conf->get('hideTrace') ? $hidden = 'hidden' : $hidden = '';
        $style    = file_get_contents($this->errorCss);
        $trace == '' ?: $style .= file_get_contents($this->traceCss);
        $traceCount = count($eObj->getTrace());

        ob_start();
        include($this->errorTpl);
        return ob_get_clean();
    }

    /**
     * В зависимости от параметра 'deferredView' выводит сразу
     * ошибку в браузер, или регистрирует callback для отложенного
     * вывода.
     *
     * @return void
     */
    public function notify()
    {
        $conf = $this->configObject;

        if (!$conf->get('deferredView')) {
            echo $this->finalStringError;
            return;
        }
        $this->errorHandler->addErrorCallbackData(__CLASS__, $this->finalStringError);
        if (!static::$count) {
            $this->errorHandler->addErrorCallback(function ($callbackData) use ($conf) {
                $conf->setNotifierClass(__CLASS__);
                $hideView = $conf->get('hideView') ? 'hidden' : '';
                $errors = $callbackData[__CLASS__];
                $count = count($errors);
                include($this->wrapperTpl);
            });
            ++static::$count;
        }
    }
}
