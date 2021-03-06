<?php
/**
 * PHP error handler and debugger.
 *
 * @package   Peraleks\ErrorHandler
 * @copyright 2017 Aleksey Perevoshchikov <aleksey.perevoshchikov.n@gmail.com>
 * @license   https://github.com/peraleks/error-handler/blob/master/LICENSE.md MIT
 * @link      https://github.com/peraleks/error-handler
 */

/*********************** Перехватываемые (catchable)-- try{} catch (Throwable $e)  ******************
 * Все исключения (catchable exceptions) прерывают выполнение скрипта */

/** [1]----------------  Error ------------------ exception_handler */
//  undefined_function();

/** [1]---------------- ArithmeticError --------- exception_handler */
// intdiv(PHP_INT_MIN, -1);

/** [1]---------------- DivisionByZeroError ----- exception_handler
 * будет сгенерировано делением по модулю на ноль,
 * обычное деления на ноль генерирует WARNING и
 * соответственно не может быть перехвачено
 * http://php.net/manual/ru/migration70.incompatible.php#migration70.incompatible.integers.div-by-zero */
// $i = 2 / 0;
// $i = 2 % 0;

/** [1]---------------- AssertionError ---------- exception_handler
 * должны быть включены zend.assertions,
 * assert.exception, assert.active */
// ini_set('assert.exception', 1);
// ini_set('zend.assertions', 1);
// ini_set('assert.active', 1);
// assert('3 < 1', '3 < 1');

/** [4]---------------- ParseError -------------- exception_handler */
// eval('-');
// HHVM parse error

/** [1]---------------- TypeError --------------- exception_handler
 * различное поведение strict_types=0/1 */
// declare(strict_types=1);
// (function (string $a) :string {})(12);



/** [1]---------------- Exception --------------- exception_handler */
// throw new \Exception('exception');

/** [1]---------------- SPL Exception ----------- exception_handler
 (выбрасываются пользователем) */
// throw new \BadFunctionCallException();
// throw new \BadMethodCallException();
// throw new \DomainException();
// throw new \InvalidArgumentException();
// throw new \LengthException();
// throw new \LogicException();
// throw new \OutOfBoundsException();
// throw new \OutOfRangeException();
// throw new \OverflowException();
// throw new \RangeException();
// throw new \RuntimeException();
// throw new \UnderflowException();
// throw new \UnexpectedValueException();

/** [1]---------------- ErrorException ---------- exception_handler
 * объект для конвертации ошибки в исключение*/
// throw new \ErrorException('текст ошибки', 1, E_ERROR, 'имя файла', 35);




/***************** Неперехватываемые (uncatchable) конструкцией -- try{} catch() *******************/
try {
/***************** Не прерывающие выполнение скрипта  *********************/
/** [2]---------------- E_WARNING --------------- error_handler */
//   $i = 2 / 0;

/** [8]---------------- E_NOTICE ---------------- error_handler */
// echo $undefined_var;

/** [32]--------------- E_CORE_WARNING ---------- shutdown_function */
//

/** [128]-------------- E_COMPILE_WARNING ------- shutdown_function */
// declare(foo='bar');

/** [512]-------------- E_USER_WARNING ---------- error_handler */
// trigger_error("E_USER_WARNING", E_USER_WARNING);

/** [1024]------------- E_USER_NOTICE ----------- error_handler */
// trigger_error("E_USER_NOTICE", E_USER_NOTICE);

/** [2048]-------------- E_STRICT ---------------
 * В PHP7 все сообщения E_STRICT переквалифицированы по другим уровням.
 * Константа E_STRICT сохранена, так что error_reporting(E_ALL|E_STRICT) не вызовет ошибки.
 * http://php.net/manual/ru/migration70.incompatible.php#migration70.incompatible.error-handling.strict */

/** [8192]------------- E_DEPRECATED ------------ error_handler */
// class a { function b(){} }; a::b();

/** [16384]------------ E_USER_DEPRECATED ------- error_handler */
// trigger_error("E_USER_DEPRECATED", E_USER_DEPRECATED);



/******************** Прерывающие выполнение скрипта *****************************/
/** [1]---------------- E_ERROR ----------------- shutdown_function */
// interface x { function y(); } class v implements x{} function n(x $x) {}(new v);

/** [16]--------------- E_CORE_ERROR ------------ shutdown_function */
// class a implements Traversable {}

/** [64]--------------- E_COMPILE_ERROR --------- shutdown_function */
// isset(NULL);
// class A{function a(){class B{}}}
// class x{} class x{}

/** [256]-------------- E_USER_ERROR ------------ error_handler
 * Если зарегистрирована error_handler, выполнение скрипта не будет прервано.
 * peraleks/error-handler, являясь глобальным обработчиком,
 * прервёт скрипт после обработки. */
// trigger_error("E_USER_ERROR", E_USER_ERROR);

/** [4096]-------------- E_RECOVERABLE_ERROR ---- error_handler
 * Если зарегистрирована error_handler, выполнение скрипта не будет прервано.
 * peraleks/error-handler, являясь глобальным обработчиком,
 * прервёт скрипт после обработки. */
// class R{} echo new R();

} catch (Throwable $e) {
    echo "\n перехвачено | caught -- \n<pre>".$e.'</pre>';
}
echo "\nВыполнение скрипта не прервано | Script execution is not interrupted \n";
