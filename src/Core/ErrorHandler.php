<?php
declare(strict_types=1);

namespace Peraleks\ErrorHandler\Core;

class ErrorHandler
{
    /**
     * @var \Peraleks\ErrorHandler\Core\ErrorHandler
     */
    static private $instance;

    /**
     * @var \Peraleks\ErrorHandler\Core\Helper
     */
    private $helper;

    /**
     * Путь к файлу конфигурации.
     *
     * @var string
     */
    private $configFile;

    /**
     * Сюда будем складывать ошибки для
     * отложенного вывода при помощи callback функций.
     *
     * @var array
     */
    private $callbackData = [];

    /**
     * Callback функции для отложенной
     * обработки и вывода ошибок.
     *
     * @var array
     */
    private $errorCallbacks = [];

    /**
     * Любые пользовательские функции,
     * которые требуется выполнить в shutdown function.
     *
     * @var array
     */
    private $userCallbacks = [];

    /**
     * Последняя ошибка полученная $this->exception().
     *
     * @var \Throwable
     */
    private $lastError;

    /**
     * ErrorHandler constructor.
     *
     * Регистрирует функции-обработчики ошибок.
     *
     * @param null $configFile
     */
    private function __construct($configFile = null)
    {
        ini_set('display_errors', 'Off');
        set_error_handler([$this, 'error']);
        set_exception_handler([$this, 'exception']);
        register_shutdown_function([$this, 'shutdown']);
        $this->configFile = $configFile;
    }

    /**
     * Singleton.
     *
     * @param null $configFile string Путь к файлу конфигурации
     * @return ErrorHandler
     */
    public static function instance($configFile = null)
    {
        return self::$instance ?? self::$instance = new self($configFile);
    }

    /**
     * Обработчик ошибок.
     *
     * Конвертирует полученную ошибку в объект исключения
     * и передаёт в обработчик исключений.
     *
     * @param $code int код уровня ошибки
     * @param $message string сообщение ошибки
     * @param $file string файл, где произошла ошибка
     * @param $line int строка ошибки
     * @return bool true
     */
    public function error($code, $message, $file, $line)
    {
        $this->exception(new \ErrorException($message, $code, $code, $file, $line), 'error handler');
        return true;
    }

    /**
     * Обработчик исключений.
     *
     * Инстанцирует помощника и передаёт ему объект ошибки
     * для дальнейшей обработки.
     *
     * @param \Throwable $e объект ошибки
     * @param string $handler название функции обработчика ('error handler' |
     * 'exception handler' | 'shutdown function')
     */
    public function exception(\Throwable $e, string $handler = 'exception handler')
    {
        $this->lastError = $e;
        if (!$this->helper) {
            $this->helper = new Helper($this->configFile, $this);
            $this->helper->createConfigObject();
        }
        $this->helper->handle($e, $handler);

    }

    /**
     * Shutdown function.
     *
     * Вылавливает из буфера последнюю фатальную ошибку,
     * котвертирует в исключение и передаёт в обработчик исключений
     * Инициирует выполнение пользовательских callbacks,
     * и callbacks отложенного вывода ошибок.
     */
    public function shutdown()
    {
        if ($el = error_get_last()) {
            $e = new \ErrorException($el['message'], $el['type'], $el['type'], $el['file'], $el['line']);

            /* передаём внутренние фатальные ошибки в отдельный обработчик,
             * для вывода и логирования, также предварительно передаём последнюю ошибку,
             * чтобы не потерять её, так как скорее всего она не была обработана
             * из-за фатальной ошибки */
            if ($this->helper && $this->helper->getInnerShutdownFatal()) {
                $this->helper->exception($this->lastError);
                $this->helper->exception($e);
            } else {
                $this->exception($e, 'shutdown function');
            }
        }
        if ($this->userCallbacks) {
            $this->invokeCallbacks($this, $this->userCallbacks);
        }
        $this->invokeDeferred();
    }

    /**
     * Выводит все саккумулированные за время выполнения ошибки.
     *
     * Если используется fastcgi_finish_request() для завершения
     * обработки запроса, можно предварительно вызвать данный метод
     * для вывода в браузер информации из errorCallbacks.
     */
    public function invokeDeferred()
    {
        if ($this->errorCallbacks) {
            $this->invokeCallbacks($this->helper, $this->errorCallbacks, $this->callbackData);
        }
    }

    /**
     * Выполняет  callbacks.
     *
     * Так как обработчики зарегистрированные в ErrorHandler не работают
     * в shutdown function, для безапасного выполнения callbacks регистрируется
     * новый обработчик ошибок, исключения тоже перенаправляются в новый обработчик.
     *
     * @param $handlerObj ErrorHandler | Helper
     * @param $callbacks array callbacks
     * @param null $data array саккумулированные данные ошибок
     */
    private function invokeCallbacks($handlerObj, array $callbacks, $data = null)
    {
        foreach ($callbacks as $callback) {
            try {
                set_error_handler([$handlerObj, 'error']);

                call_user_func($callback, $data);

            } catch (\Throwable $e) {
                $handlerObj->exception($e);
            } finally {
                restore_error_handler();
            }
        }
    }

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
    public function addErrorCallbackData(string $key, $value)
    {
        $this->callbackData[$key][] = $value;
    }

    /**
     * Регистрирует callback функцию для отложенной обработки ошибок.
     *
     * Метод будет вызван в shutdown function. Аргументом будет передан
     * массив данных, сохранённых при помощи addErrorCallbackData()
     *
     * @param callable $callback функция для обработки и вывода ошибок
     * @return void
     */
    public function addErrorCallback(callable $callback)
    {
        $this->errorCallbacks[] = $callback;
    }

    /**
     * Регистрирует пользовательский callback, чтобы
     * позже он был выполен в shutdown function.
     * Фатальные ошибки в этом callback не могут быть
     * обработаны данным обработчиком, так как выполняются
     * в shutdown function.
     *
     * @param callable $callback callback
     */
    public function addUserCallback(callable $callback)
    {
        $this->userCallbacks[] = $callback;
    }
}
