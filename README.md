[![Latest Stable Version](https://poser.pugx.org/arrilot/bitrix-hlblock-fields-fixer/v/stable.svg)](https://packagist.org/packages/arrilot/bitrix-hlblock-fields-fixer/)
[![Total Downloads](https://img.shields.io/packagist/dt/arrilot/bitrix-hlblock-fields-fixer.svg?style=flat)](https://packagist.org/packages/Arrilot/bitrix-hlblock-fields-fixer)

# Модификация полей создаваемых модулем highloadblock

## Введение

Как известно, модуль highloadblock хранит элементы в произвольной таблице, причем каждое поле highload-блока представляет из себя столбец в этой таблице.
Однако по историческим причинам, Битрикс весьма странно выбирает тип столбца под свойство, например строковое свойство хранится в столбце с типом text.
Данный пакет позволяет переопределить типы полей для любых свойств хайлоадблоков, а также выполняет ряд самых полезных преобразований по-умолчанию.

## Установка

1)```composer require arrilot/bitrix-hlblock-fields-fixer```

2) добавляем в init.php

```php
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

Arrilot\BitrixHLBlockFieldsFixer\Provider::register();
```

## Использование

По-умолчанию, пакет производит следующие преобразования:

```php
//  тип поля => тип столбца в mysql
// 'string' => 'varchar(255)',
// 'string_formatted' => 'varchar(255)',
// 'text' => 'text',
// 'boolean' => 'tinyint(1)',
```

string - строка
string_formatted - шаблон
text - строка/шаблон в случае если при создании в поле "Количество строчек поля ввода:" указано более 1.

Можно добавить дополнительные или переписать существующие при помощи массива конфигурации:

```php
Arrilot\BitrixHLBlockFieldsFixer\Provider::register(['text' => 'longtext']);
```
Данная строчка затронет лишь преобразования для поля text, все остальные преобразования по-умолчанию продолжат работать.
Для того чтобы выключить какое-то преобразование можно задать ему null.

## Как это работает

На событие `OnAfterUserTypeAdd` добавлен обработчик который выполняет `ALTER TABLE MODIFY COLUMN`
Выполняется он только при добавлении нового свойства. При обновлении ничего испортить невозможно.