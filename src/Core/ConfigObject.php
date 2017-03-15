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

use Peraleks\ErrorHandler\Exception\ErrorHandlerException;

/**
 * Class ConfigObject
 *
 * Валидирует конфигурационный файл.
 * Предоставляет остальным классам доступ к параметрам конфигурации.
 */
class ConfigObject
{
    /**
     * Начальная конфигураця для слияния
     * с пользовательской конфигурацией.
     *
     * @var array
     */
    private $config = [
        'SELF_LOG_FILE'   => '',
        'ERROR_REPORTING' => E_ALL,
        'NOTIFIERS'       => [],
        'APP_DIR'         => '',
        'MODE'            => 'prod',
    ];

    /**
     * Имя класса-уведомителя.
     *
     * $this->get() будет искать значения в масиве конфигурации
     * по этому имени и переданному ключу
     *
     * @var string
     */
    private $currentNotifier;

    /**
     * ConfigObject constructor.
     *
     * Выполняет валидацию файла конфигурации.
     *
     * @param string $file полное имя файла конфигурации
     * @throws ErrorHandlerException
     */
    public function __construct($file)
    {
        if (!is_string($file)) {
            throw new ErrorHandlerException(
                'ErrorHandler::instance($file): $file must be a string, '.gettype($file).' given'
            );
        } elseif (!file_exists($file)) {
            throw new ErrorHandlerException(
                'Configuration file not exist: ErrorHandler::instance('.$file.')'
            );
        } elseif (!is_array($arr = include $file)) {
            throw new ErrorHandlerException(
                'The configuration file '.$file.' should return an array, '.gettype($arr).' given'
            );
        }
        $this->config = array_merge($this->config, $arr);
        $this->validateSelfLogFile($this->config['SELF_LOG_FILE']);
        $this->errorReportingValidate($this->config['ERROR_REPORTING']);
        $this->notifiersValidate($this->config['NOTIFIERS']);
        $this->appDirValidate($this->config['APP_DIR']);
        $this->modeValidate($this->config['MODE']);
    }

    /**
     * Валидация параметра конфигурации 'SELF_LOG_FILE'.
     *
     * Значение по умолчанию: пустая строка
     *
     * @param $selfLogFile
     */
    private function validateSelfLogFile(&$selfLogFile)
    {
        is_string($selfLogFile) ?: $selfLogFile = '';
    }

    /**
     * Валидация параметра конфигурации 'ERROR_REPORTING'.
     *
     * Значение по умолчанию: E_ALL
     *
     * @param $eReporting
     */
    private function errorReportingValidate(&$eReporting)
    {
        is_int($eReporting) ?: $eReporting = E_ALL;
    }

    /**
     * Валидация параметра конфигурации 'NOTIFIERS'.
     *
     * Значение по умолчанию: array
     *
     * @param $notifiers
     */
    private function notifiersValidate(&$notifiers)
    {
        if (!is_array($notifiers)) {
            $type = gettype($notifiers);
            $notifiers = [];
            trigger_error(
                'Configuration file: value of key \'NOTIFIERS\' must be an array, '.$type.' given',
                E_USER_ERROR
            );
        }
    }

    /**
     * Валидация параметра конфигурации 'APP_DIR'.
     *
     * Данный параметр нужен только для сокращённого отображения
     * пути файлов, и на логику обработчика ни как не влияет.
     * Значение по умолчанию: пустая строка для CLI,
     * и dirname($_SERVER['DOCUMENT_ROOT']) для остальных режимов.
     *
     * @param string|mixed $appDir
     */
    private function appDirValidate(&$appDir)
    {
        if (!is_string($appDir) || 'default' === $appDir) {
            /* нормализуем обратный слеш в обычный так же, как это делает PHP в $_SERVER*/
            $appDir = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'] ?? ''));
        } else {
            $appDir = str_replace('\\', '/', $appDir);
        }
    }

    /**
     * Валидация параметра конфигурации 'MODE'.
     *
     * Значение по умолчанию: 'prod'
     *
     * @param string|mixed $mode
     */
    private function modeValidate(&$mode)
    {
        if ('dev' !== $mode) $mode = 'prod';
    }

    /**
     * Устанавливает имя текущего уведомителя
     *
     * get() будет искать значения в масиве конфигурации
     * по этому имени и переданному ей ключу
     *
     * @param string $notifierClass имя класса уведомителя
     */
    public function setNotifierClass(string $notifierClass)
    {
        $this->currentNotifier = $notifierClass;
    }

    /**
     * Возвращает массив уведомителей.
     *
     * @return array массив уведомителей
     */
    public function getNotifiers(): array
    {
        return $this->config['NOTIFIERS'];
    }

    /**
     * Возвращает значение ключа конфигурации 'ERROR_REPORTING'.
     *
     * @return int
     */
    public function getErrorReporting(): int
    {
        return $this->config['ERROR_REPORTING'];
    }

    /**
     * Возвращает значение из конфигурационного массива по переданному ключу.
     *
     * Ищет значенияе в масиве конфигурации по двум ключам: по переданному,
     * и по имени умедомителя из $this->currentNotifier,
     * предварительно установленного setNotifierClass().
     * В случае неудачи возвращает null.
     *
     * @param string $param ключ массива конфигурации
     * @return null | string
     */
    public function get(string $param)
    {
        return $this->config['NOTIFIERS'][$this->currentNotifier][$param] ?? null;
    }

    /**
     * Возвращает значение конфигурации 'APP_DIR'.
     *
     * @return string
     */
    public function getAppDir(): string
    {
        return $this->config['APP_DIR'];
    }

    /**
     * Вщзвращает значение конфигурации 'MODE'.
     *
     * Не определяет режим CLI. Для этого воспользуйтесь
     * на месте выражением if (PHP_SAPI === 'cli')
     *
     * @return string 'prod' | 'dev'
     */
    public function getMode(): string
    {
        return $this->config['MODE'];
    }

    /**
     * Вщзвращает значение конфигурации 'SELF_LOG_FILE'.
     *
     * @return string если не задано, то пустая строка
     */
    public function getSelfLogFile(): string
    {
        return $this->config['SELF_LOG_FILE'];
    }
}
