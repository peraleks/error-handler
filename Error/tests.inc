<?php
 /************* Перехватываемые ошибки -- try{} catch (Throwable $e) ***************************/
/** [1] Error */
//undefined_function();

/** [1] ArithmeticError */
//intdiv(PHP_INT_MIN, -1);

/** [1] DivisionByZeroError
 * будет сгенерировано делением по модулю на ноль,
 * обычное деления на ноль генерирует WARNING и
 * соответственно не может быть перехвачено */
//$i = 2 / 0;
//$i = 2 % 0;

/** [1] AssertionError
 * должны быть включены zend.assertions,
 * assert.exception, assert.active */
//ini_set('assert.exception', 1);
//ini_set('zend.assertions', 1);
//ini_set('assert.active', 1);
//assert('3 < 1', '3 < 1');

/** [4] ParseError */
//eval('-');

/** [1] TypeError
 * различное поведение strict_types=0/1 */
//declare(strict_types=1);
//(function (string $a) :string {})(12);



/** [1] Exception */
//throw new Exception('exception');

/** SPL Exception */
//throw new BadFunctionCallException();
//throw new BadMethodCallException();
//throw new DomainException();
//throw new InvalidArgumentException();
//throw new LengthException();
//throw new LogicException();
//throw new OutOfBoundsException();
//throw new OutOfRangeException();
//throw new OverflowException();
//throw new RangeException();
//throw new RuntimeException();
//throw new UnderflowException();
//throw new UnexpectedValueException();

/** [1] ErrorException
 * исключение для конвертации ошибки в исключение*/
//throw new ErrorException('текст ошибки', 1, E_ERROR, 'имя файла', 35);




/** Неперехватываемые ошибки конструкцией -- try{} catch() *********************************/
try{
/***************** Не прерывающие выполнение скрипта (NONFATAL) *********************/
/** [2]---------------- E_WARNING */
//$i = 2 / 0;

/** [8]---------------- E_NOTICE */
//echo $undefined_var;

/** [128]-------------- E_COMPILE_WARNING */
//declare(foo='bar');

/** [512]-------------- E_USER_WARNING */
// trigger_error("E_USER_WARNING", E_USER_WARNING);

/** [1024]------------- E_USER_NOTICE */
// trigger_error("E_USER_NOTICE", E_USER_NOTICE);


/** [2048]-------------- E_STRICT */
//

/** [8192]------------- E_DEPRECATED */
// class c {function f(){}} c::f();

/** [16384]------------ E_USER_DEPRECATED */
// trigger_error("E_USER_DEPRECATED", E_USER_DEPRECATED);



/******************** Прерывающие выполнение скрипта (FATAL) ***********************/
/** [16]--------------- E_CORE_ERROR */
//class a implements Traversable {}

/** [32]--------------- E_CORE_WARNING*/
//

/** [64]--------------- E_COMPILE_ERROR */
//isset(NULL);

/** [256]-------------- E_USER_ERROR */
// trigger_error("E_USER_ERROR", E_USER_ERROR);

/** [4096]-------------- E_RECOVERABLE_ERROR */
//class A{} echo new A();

} catch (Throwable $e) {
    echo 'перехвачено '.$e;
}

