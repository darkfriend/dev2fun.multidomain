# StringHelper - хелпер для работы со строками 

``composer require darkfriend/php5-string``

* ```StringHelper::htmlspecialchars($val)``` - статичный метод, который делает htmlspecialchars() для строк и массивов
* ```StringHelper::htmlspecialchars_decode($val)``` - статичный метод, который делает htmlspecialchars_decode() для строк и массивов
* ```StringHelper::generateString($length, $chars)``` - статичный метод, который возвращает сгенерированную строку нужной длины
* ```StringHelper::getDeclension($value, $words)``` - статичный метод, который возвращает окончания слов при слонении. _Например: 5 товаров, 1 товар, 3 товара_
* ```StringHelper::truncate($string, $length, $suffix = '...', $encoding = null)``` - возвращает обрезанный текст в $length символов
* ```StringHelper::truncateWords($string, $count, $suffix = '...')``` - возвращает обрезанный текст в $length слов

