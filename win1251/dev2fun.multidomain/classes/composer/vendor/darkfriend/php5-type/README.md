# TypeHelper

* ```TypeHelper::toStrictType($value)``` - return $value to strict type

## Example

```php
use \darkfriend\helpers\TypeHelper;

var_dump(
    TypeHelper::toStrict('false'), // bool(false)
    TypeHelper::toStrict(null), // NULL
    TypeHelper::toStrict(0), // int(0)
    TypeHelper::toStrict('0'), // int(0)
    TypeHelper::toStrict('1'), // int(1)
    TypeHelper::toStrict(1), // int(1)
    TypeHelper::toStrict('1.1'), // float(1.1)
    TypeHelper::toStrict(1.1), // float(1.1)
    TypeHelper::toStrict(true), // bool(true)
    TypeHelper::toStrict(false), // bool(false)
    TypeHelper::toStrict('my string'), // string(9) "my string"
    TypeHelper::toStrict('') // string(0) ""
);
```