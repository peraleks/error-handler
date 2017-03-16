<a href="https://packagist.org/packages/peraleks/error-handler"><img src="https://poser.pugx.org/peraleks/error-handler/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/peraleks/error-handler"><img src="https://poser.pugx.org/peraleks/error-handler/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/peraleks/error-handler"><img src="https://poser.pugx.org/peraleks/error-handler/license.svg" alt="License"></a>
# ErrorHandler
Обработчик ошибок для PHP7 с возможностью расширения функциональности.
Поддерживает все типы ошибок и исключений. Может быть использован как в крупномасштабных приложениях,
так и в простых скриптах.

## [Полную документацию можно прочесть в Wiki][link-wiki]

## Установка
```bash
$ composer require peraleks/error-handler
```

## Простое быстрое использование
```php
require __DIR__.'/vendor/peraleks/error-handler/src/register_error_handler.inc';
```

## Проверка
Скопируйте файл error-handler/src/all_error.inc в удобное для вас место,
например рядом с индексным файлом и подключите:
```php
require __DIR__.'/all_error.inc';
```
Откройте файл и раскомментируйте строчку кода для требуемой ошибки. Обновите страницу в браузере.

Например:
```php
/** [1]----------------  Error ------------------ exception_handler */
//  undefined_function();      <-- раскомментируйте
```

## Лицензия

The MIT License ([MIT](LICENSE.md)).

[link-zip]: https://github.com/peraleks/error-handler/archive/master.zip
[link-wiki]: https://github.com/peraleks/error-handler/wiki
[link-author]: https://github.com/peraleks

