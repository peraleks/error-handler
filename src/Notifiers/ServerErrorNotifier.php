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

/**
 * Class ServerErrorNotifier
 *
 * Выводит в браузер страницу, уведомляющую пользователя
 * о том, что на сервере произошла ошибка.<br>
 * Так же отсылает соответствующие заголовки.<br>
 * Используется в режиме production.
 */
class ServerErrorNotifier extends AbstractNotifier
{
    /**
     * Полное имя файла шаблона, который будет подключен
     * если пользовательский файл не определён или в нём
     * произошла ошибка.
     *
     * @var string
     */
    protected $defaultIncludeFile;

    /**
     * Файл, который будет подключен в $this->notify().
     * Файл должен выводить результат в буфер вывода.
     * Файл не обязательно должен быть шаблоном.
     *
     * @var string
     */
    protected $includeFile;

    /**
     * Заголовок, который будет отправлен в браузер
     *
     * @var string
     */
    protected $header = 'HTTP/1.1 500 Internal Server Error';

    /**
     * Валидирует параметр конфигурации - 'header'.
     */
    protected function before()
    {
        !is_string($header = $this->configObject->get('header')) ?: $this->header = $header;
        $this->defaultIncludeFile = dirname(__DIR__).'/View/serverError500.php';
        $this->includeFile = $this->validateIncludeFile($this->configObject->get('includeFile'));
    }

    /**
     * Возвращает пустую строку - стек обрабатываться не будет.
     *
     * @return string
     */
    protected function traceFormatterClass(): string
    {
        return '';
    }

    /**
     * Валидирует имя файла для включения.
     *
     * @param $file string имя файла из конфигурации
     * @return string валидное имя файла для включения
     */
    protected function validateIncludeFile($file): string
    {
        if ('' === $file || !is_string($file)) {
            return $this->defaultIncludeFile;
        }
        if (!file_exists($file)) {
            trigger_error('ServerErrorNotifier: file '.$file.' not exist', E_USER_WARNING);
            return $this->defaultIncludeFile;
        }
        return $file;
    }

    /**
     * Подключает файл, выводящий страницу ошибки.
     * В нём будет доступен объект ошибки $errorObject.
     *
     * @param string $trace пустая строка
     * @return string страница ошибки сервера
     */
    protected function ErrorToString(string $trace): string
    {
        $errorObject = $this->errorObject;
        ob_start();
        try {
            include $this->includeFile;
        } catch (\Throwable $e) {
            trigger_error($e->getMessage().' in '.$e->getFile().':'.$e->getLine(), E_USER_WARNING);
            include $this->defaultIncludeFile;
        } finally {
            return ob_get_clean();
        }
    }

    /**
     * Отсылает заголовки и страницу ошибки сервера в браузер.
     *
     * @param string $error форматированная ошибка
     * @return true
     */
    protected function notify(string $error)
    {
        $this->clean();
        headers_sent() ?: header($this->header);
        echo $error;
        return true;
    }

    /**
     * Удаляет буферы вывода.
     */
    protected function clean()
    {
        ob_end_clean();
        if (0 < ob_get_level()) $this->clean();
    }
}