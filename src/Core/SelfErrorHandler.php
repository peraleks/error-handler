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

namespace Peraleks\ErrorHandler\Core;

/**
 * Class SelfErrorHandler
 *
 * Обработчик внутренних ошибок.
 * Реализует логирование и отображение внутренних ошибок и
 * неудачно обработанных ошибок клиентской части кода. Так же посылает
 * код состояния 500 в случае фатальной ошибки.
 */
class SelfErrorHandler
{
    /**
     * Соответствие кодов ошибок их названиям.
     *
     * @var array
     */
    private  $codeName = [
        E_ERROR             => 'ERROR',
        E_WARNING           => 'WARNING',
        E_PARSE             => 'PARSE',
        E_NOTICE            => 'NOTICE',
        E_CORE_ERROR        => 'CORE_ERROR',
        E_CORE_WARNING      => 'CORE_WARNING',
        E_COMPILE_ERROR     => 'COMPILE_ERROR',
        E_COMPILE_WARNING   => 'COMPILE_WARNING',
        E_USER_ERROR        => 'USER_ERROR',
        E_USER_WARNING      => 'USER_WARNING',
        E_USER_NOTICE       => 'USER_NOTICE',
        E_STRICT            => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED        => 'DEPRECATED',
        E_USER_DEPRECATED   => 'USER_DEPRECATED',
    ];

    /**
     * Полное имя файла лога внутренних ошибок.
     *
     * @var string
     */
    private $selfLogFile;

    /**
     * Флаг development режима.
     *
     * @var bool
     */
    private $devMode;

    /**
     * Код ошибки (severity).
     *
     * @var int
     */
    private $code;

    /**
     * Маска ошибок, для которых надо показать стек вызовов.
     *
     * @var int
     */
    private $traceEnabled = E_ERROR | E_RECOVERABLE_ERROR;

    /**
     * Маска ошибок, для которых  надо прервать скрипт
     * и отправить состояние 'HTTP/1.1 500 Internal Server Error'.
     *
     * @var int
     */
    private $error500 = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

    /**
     * SelfErrorHandler constructor.
     *
     * Валидирует имя файла собственного лога ошибок.
     * И определяет dev | prod режимы.
     *
     * @param ConfigObject|null $configObject объект конфигурации
     */
    public function __construct(ConfigObject $configObject = null)
    {
        if ($configObject && ('' !== $configObject->getSelfLogFile())) {
            $this->selfLogFile = $configObject->getSelfLogFile();
        } elseif (PHP_SAPI !== 'cli') {
            $this->selfLogFile = $_SERVER['DOCUMENT_ROOT'].'/error_handler_'
                .crc32($_SERVER['DOCUMENT_ROOT'].$_SERVER['SERVER_SOFTWARE']).'.log';
        }
        $this->devMode = $configObject && ('dev' === $configObject->getMode());
    }

    /**
     * Запускает обработку ошибки.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     * @return void
     */
    public function report($e)
    {
        /* Определяем код ошибки для исключения \ParseError - E_PARSE,
        для остальных исключений - E_ERROR */
        if (!$e instanceof \ErrorException && !$e instanceof ErrorObject) {
            $this->code = $e instanceof \ParseError ? E_PARSE : E_ERROR;
        } else {
            $this->code = $e->getCode();
        }

        if (PHP_SAPI === 'cli') {
            $this->cliReport($e);
            return;
        }
        if ($this->devMode) {
            $this->htmlReport($e);
            $this->fileReport($e, $this->selfLogFile);
        } else {
            $this->fileReport($e, $this->selfLogFile);
        }
    }

    /**
     * Выводит сообщение ошибки в CLI режиме.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     */
    private function cliReport($e)
    {
        echo "\n\033[32m".$this->getStringError($e)."\033[0m\n";
    }

    /**
     * Выводит сообщение ошибки в браузер.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     */
    private function htmlReport($e)
    {
        $type    = $this->getType($e);
        $file    = $e->getFile();
        $line    = $e->getLine();
        $message = $e->getMessage();
        $trace   = $this->code & $this->traceEnabled ? '<pre>'.$e->getTraceAsString().'</pre>' : '';

        include dirname(__DIR__).'/View/selfError.tpl.php';
    }

    /**
     * Пишет ошибку в файл.
     *
     * Eсли требуется, отправляет состояние 500 с последующим
     * прерыванием выполнения скрипта.
     *
     * @param \Throwable|ErrorObject $e    объект ошибки
     * @param string                 $file полное имя вайла лога внутренних ошибок
     */
    private function fileReport($e, string $file)
    {
        if ($r = fopen($file, 'ab')) {
            fwrite($r, "\n[".date('d-M-o H:i:s O').'] '.$this->getStringError($e)."\n");
            fclose($r);
        }

        /* Если $e->getCode() вернёт 0 (\Throwable $e), значит ошибка сгенерирована
         * внутри обработчика и была перехвачена - поэтому скрипт не останавливаем
         * и не посылаем состояние 500.
         * Если $e является экземпляром ErrorObject, значит ошибка была в клиентской
         * части кода. ErrorObject конвертирует коды ошибок и никогда не возвращает 0.
         * В этом случае при совпадении кода с маской $this->error500 и в режиме production
         * посылаем состояние 500 и останавливаем скрипт.
         * Если фатальная ошибка произошла внутри обработчика, аналогично - 500 и exit */
        if (!$this->devMode && ($e->getCode() & $this->error500)) {
            $this->clean();
            headers_sent() ?: header('HTTP/1.1 500 Internal Server Error');
            include dirname(__DIR__).'/View/serverError500.php';
            exit;
        }
    }

    /**
     * Удаляет буферы вывода.
     */
    private function clean()
    {
        ob_end_clean();
        if (0 < ob_get_level()) $this->clean();
    }

    /**
     *  Возвращает тип (название) ошибки.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     * @return string
     */
    private function getType($e): string
    {
        if ($e instanceof \ErrorException) {
            return $this->codeName[$e->getCode()] ?? 'unknown';
        }
        return get_class($e);
    }

    /**
     * Возвращает конечную строку ошибки
     * со стеком вызовов или без.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     * @return string
     */
    private function getStringError($e): string
    {
        if (!($this->code & $this->traceEnabled)) {
            return $this->getType($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine();
        }
        return (string)$e;
    }

}