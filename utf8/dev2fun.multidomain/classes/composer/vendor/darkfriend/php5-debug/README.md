# Debug, Log, Trace helpers

``composer require darkfriend/php5-debug``

Классы помощники для PHP5
* для трассировки и логирования данных
* для дебага разных частей кода

## Trace
Трассировка данных в файл

* ``` Trace::init($sessionHash = null, $mode = null, $pathLog = '/') ``` - инициализация, указывается только для изменения первоначальных или ранее определенных настроек
* ``` Trace::add($message, $category = 'common') ``` - добавление сообщения в файл

### Trace Example
```php
// basic usage
\darkfriend\helpers\Trace::add('my message'); // добавляет строку
// \darkfriend\helpers\Trace::add(['my message']); // добавляем любой массив или объект
```

```php
// example: set trace file
\darkfriend\helpers\Trace::init(
    null,
    null,
    './logs/trace.log'
);
\darkfriend\helpers\Trace::add('my message'); // добавляет строку в файл trace.log
// \darkfriend\helpers\Trace::add(['my message']); // добавляем любой массив или объект в файл trace.log

\darkfriend\helpers\Trace::init(
    null,
    null,
    './logs/custom.log'
);
\darkfriend\helpers\Trace::add('my message'); // добавляет строку в файл custom.log
// \darkfriend\helpers\Trace::add(['my message']); // добавляем любой массив или объект в файл custom.log
```

## Log
Логированные данных в файл

* ``` Trace::init($sessionHash = null, $mode = null, $pathLog = '/') ``` - инициализация, указывается только для изменения первоначальных или ранее определенных настроек
* ``` Trace::add($message, $category = 'common') ``` - добавление сообщения в файл

### Trace Example
```php
// basic usage
\darkfriend\helpers\Log::add('my message'); // добавляет строку
// \darkfriend\helpers\Trace::add(['my message']); // добавляем любой массив или объект
```

```php
// example: set trace file
\darkfriend\helpers\Log::init(
    null,
    null,
    './logs/basic.log'
);
\darkfriend\helpers\Log::add('my message'); // добавляет строку в файл basic.log
// \darkfriend\helpers\Trace::add(['my message']); // добавляем любой массив или объект в файл basic.log

\darkfriend\helpers\Log::init(
    null,
    null,
    './logs/custom.log'
);
\darkfriend\helpers\Log::add('my message'); // добавляет строку в файл custom.log
// \darkfriend\helpers\Trace::add(['my message']); // добавляем любой массив или объект в файл custom.log
```

## Debug (alias DebugHelper)

* ```DebugHelper::$mainKey``` - свойство, содержащее имя ключа для $_COOKIE и $_GET
* ```DebugHelper::print_pre($o,$die,$show)``` - статичный метод, который выводит всю структуру массива и объекта, с информацией о файле и строке (подробности ниже)
* ```DebugHelper::call($func,$params)``` - статичный метод, который вызывает переданную функцию только у админа, передавая нужные параметры (подробности ниже)
* ```DebugHelper::trace($message,$category='common')``` - статичный метод трессировки (ниже примеры использования)

## DebugHelper::print_pre($o,$die,$show);
* $o - данные, которые надо вывести
* $die - прерывать ли после вывода выполнение скрипта (по умолчанию false)
* $show - выводить всем [или только в определенных случаях] (по умолчанию true)

### Пример
```php
use \darkfriend\devhelpers\DebugHelper;
$data = [
  'key1' => 'value1',
  'key2' => 'value2',
  'key3' => [
    'subKey1' => 'subValue1',
    'subKey2' => 'subValue2',
  ],
];
DebugHelper::print_pre($data);
```

## DebugHelper::call($func,$params)
* $func - функция, которую надо вывести
* $params - массив параметров которые надо передать

### Пример
```php
use \darkfriend\helpers\DebugHelper;
$data = [
  'key1' => 'value1',
  'key2' => 'value2',
  'key3' => [
    'subKey1' => 'subValue1',
    'subKey2' => 'subValue2',
  ],
];

// способ 1: используя $params
DebugHelper::call(function($data) {
  DebugHelper::print_pre($data);
},$data);

// способ 2: используя use
DebugHelper::call(function() use ($data) {
  DebugHelper::print_pre($data);
});
```

## DebugHelper::trace($message,$category)
* $message - сообщение
* $category - категория трассировки

### Дополнительные возможности

* поддержка режимов трассировния
    * TRACE_MODE_REPLACE - режим перезаписи лога
    * TRACE_MODE_APPEND - режим дополнение лога
    * TRACE_MODE_SESSION - режим trace-сессии
* поддержка trace-сессий - каждый запуск в отдельном 


### Example 1: простая трассировка
_Задача: Простая запись данных в лог_
```php
use \darkfriend\helpers\DebugHelper;
$array1 = [
  'key1' => 'value1',
  'key2' => 'value2'
];

// trace 1
DebugHelper::trace($array1);
// итог: запишет $array1 с категорией common.

$array1['key3'] = [
  'subKey1' => 'subValue1',
  'subKey2' => 'subValue2',
];

// trace 2
DebugHelper::trace($array1);
// итог: допишет в лог обновленный $array1 с категорией common
```

#### Example 1: FAQ

* _Где лежит файл?_ - путь ``$_SERVER['DOCUMENT_ROOT].'/trace.log'``
* _Что будет в логе?_ - будет 2 записи переменной $array1. По умолчанию идет запись лога сверху вниз
* _Какая категория будет?_ - по умолчанию категория "common"

### Example 2: каждый запуск в отдельный файл
_Задача: Мы сохраняем данные и хотим трассировать id-строки и сохраняемые данные_

```php
use \darkfriend\helpers\DebugHelper;

$id = 1; // идентификатор

// делаем инициализацию
// $id - ключ trace-session
// self::TRACE_MODE_SESSION - включаем режим trace-session
DebugHelper::traceInit($id, DebugHelper::TRACE_MODE_SESSION);

$array1 = [
  'key1' => 'value1',
  'key2' => 'value2',
  'key3' => 'value3'
];

DebugHelper::trace($array1);
// итог: запишет $array1 с категорией common.

$array1['key3'] = [
  'subKey1' => 'subValue1',
  'subKey2' => 'subValue2',
];

// trace 2
DebugHelper::trace($array1);
// итог: допишет в лог обновленный $array1 с категорией common
```

#### Example 2: FAQ

* _Где лежит файл?_ - путь ``$_SERVER['DOCUMENT_ROOT]."/{$id}-trace.log"``
* _Что будет в логе?_ - будет 2 записи переменной $array1. По умолчанию идет запись лога сверху вниз
* _Какая категория будет?_ - по умолчанию категория "common"
* _Как изменить путь до лога?_ - по умолчанию лог создается в корне, чтоб его изменить, нужно передать путь от корня в 3-ий параметр метода DebugHelper::traceInit(). Пример: ``DebugHelper::traceInit($id, self::TRACE_MODE_SESSION,'/logs')``
* _Могу ли я для одного trace сделать один файл, для другого - другой?_ - да, нужно в нужный момент вызвать метод ``DebugHelper::setHashSession($hash)``, где $hash - это любой ключ.