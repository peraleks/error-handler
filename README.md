<a href="https://packagist.org/packages/peraleks/error-handler"><img src="https://poser.pugx.org/peraleks/error-handler/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/peraleks/error-handler"><img src="https://poser.pugx.org/peraleks/error-handler/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/peraleks/error-handler"><img src="https://poser.pugx.org/peraleks/error-handler/license.svg" alt="License"></a>
# ErrorHandler
ErrorHandler - обработчик ошибок для PHP 7 с возможностю расширения функциональности.
Может быть использован как в крупномасштабных приложениях, так и в простых скриптах.
Делает отладку лёгкой и обладает приятным лаконичным дизайном.

## Установка
При помощи composer:

```bash
$ composer require peraleks/error-handler
```
Также вы можете просто скачать и распаковать [ZIP-архив][link-zip].
ErrorHandler имеет свой собственный автозагрузчик классов, поэтому будет работать
без composer.

## Простое быстрое использование
Если установка была произведена при помощи composer и ваш индексный файл находится в корне приложения:
```php
require __DIR__.'/vendor/peraleks/error-handler/src/register_error_handler.inc';
```
Если вы скачали [ZIP-apхив][link-zip] и распаковали его рядом с индексным файлом:
```php
require __DIR__.'/error-handler/src/register_error_handler.inc';
```

## Проверка
Скопируйте файл error-handler/src/all_error.inc в удобное для вас место,
например рядом с индексным файлом и подключите:
```php
require __DIR__.'/all_error.inc';
```
Откройте файл и раскомментируйте строчку кода (двойной слеш) для требуемой ошибки. Обновите страницу в браузере.

## [Полноценная интеграция в приложение (Wiki)][link-wiki]

## Лицензия

The MIT License ([MIT](LICENSE.md)).

[link-zip]: https://github.com/peraleks/error-handler/archive/master.zip
[link-wiki]: https://github.com/peraleks/error-handler/wiki
[link-author]: https://github.com/peraleks

