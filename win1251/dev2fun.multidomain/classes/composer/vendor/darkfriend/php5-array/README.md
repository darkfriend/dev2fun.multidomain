# ArrayHelper - хелпер для удобной работы с массивами

``composer require darkfriend/php5-array``

* ``ArrayHelper::in_array($needle, $haystack)`` - highload method for search value in array
* ``ArrayHelper::isMulti($arr)`` - check array on multiple array
* `` ArrayHelper::sortValuesToArray($sourceArray,$orderArray)`` - Sort values array to order array
* `` ArrayHelper::sortKeysToArray($sourceArray,$orderArray)`` - Sort keys source array to order array
* `` ArrayHelper::keysExists($arKeys, $sourceArray)`` - found keys in source array
* `` ArrayHelper::removeByKey(array $source, array $keys, bool $negative = false)`` - remove keys from source array. If set negative in true then remove all except for keys
* `` ArrayHelper::removeByValue(array $source, array $values, bool $negative = false)`` - found values and remove item from source array. If set negative in true then remove all except for values