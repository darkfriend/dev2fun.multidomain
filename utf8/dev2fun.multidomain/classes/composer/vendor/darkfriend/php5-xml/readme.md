# PHP5 XML helper

* Array to XML (``` XML::encode() ```)
* XML to Array (``` XML::decode() ```)

## How to install

```
composer require darkfriend/php5-xml
```

## How to use

### Array to XML (encode)

```php
$array = array(
    'bar' => 'value bar',
    'foo' => 'value foo',
    'der' => array(
        '@cdata' => 'this is long text',
        '@attributes' => array(
            'at1' => 'at1val',
            'at2' => 'at2val',
        ),
    ),
    'qpo' => array(
        'sub1' => array('sub2'=>'val')
    )
);

echo \darkfriend\helpers\Xml::encode($array);
```

#### Result encode

```xml
<?xml version="1.0" encoding="utf-8"?>
<root>
    <bar>value bar</bar>
    <foo>value foo</foo>
    <der at1="at1val" at2="at2val"><![CDATA[this is long text]]></der>
    <qpo>
        <sub1>
            <sub2>val</sub2>
        </sub1>
    </qpo>
</root>
```

### Xml string to Array (decode)

```php
$xml = '<?xml version="1.0"?>
    <root>
        <bar>value bar</bar>
        <foo>value foo</foo>
        <der at1="at1val" at2="at2val"><![CDATA[this is long text]]></der>
        <qpo>
            <sub1>
                <sub2>val</sub2>
            </sub1>
        </qpo>
    </root>
';

var_dump(\darkfriend\helpers\Xml::decode($xml));
```

#### Result decode

```
Array
(
    [bar] => value bar
    [foo] => value foo
    [der] => Array
        (
            [@attributes] => Array
                (
                    [at1] => at1val
                    [at2] => at2val
                )
            [@value] => this is long text
        )
    [qpo] => Array
        (
            [sub1] => Array
                (
                    [sub2] => val
                )
        )
)
```

### Custom \<?xml \?> attributes

```php
$array = array(
    'bar' => 'value bar',
    'foo' => 'value foo',
    'der' => array(
        '@cdata' => 'this is long text',
        '@attributes' => array(
            'at1' => 'at1val',
            'at2' => 'at2val',
        ),
    ),
    'qpo' => array(
        'sub1' => array('sub2'=>'val')
    )
);

echo \darkfriend\helpers\Xml::encode(
    $array,
    [
        'header' => [
            'version' => 1.0,
            'encoding' => 'utf-8',
        ],
    ]
);
```

```xml
<?xml version="1.0" encoding="utf-8"?>
<root>
    <bar>value bar</bar>
    <foo>value foo</foo>
    <der at1="at1val" at2="at2val"><![CDATA[this is long text]]></der>
    <qpo>
        <sub1>
            <sub2>val</sub2>
        </sub1>
    </qpo>
</root>
```

### Custom root element

```php
$array = array(
    'bar' => 'value bar',
    'foo' => 'value foo',
    'der' => array(
        '@cdata' => 'this is long text',
        '@attributes' => array(
            'at1' => 'at1val',
            'at2' => 'at2val',
        ),
    ),
    'qpo' => array(
        'sub1' => array('sub2'=>'val')
    )
);

echo \darkfriend\helpers\Xml::encode(
    $array,
    [
        'root' => '<response/>',
        'header' => [
            'version' => 1.0,
            'encoding' => 'utf-8',
        ],
    ]
);
```

```xml
<?xml version="1.0" encoding="utf-8"?>
<response>
    <bar>value bar</bar>
    <foo>value foo</foo>
    <der at1="at1val" at2="at2val"><![CDATA[this is long text]]></der>
    <qpo>
        <sub1>
            <sub2>val</sub2>
        </sub1>
    </qpo>
</response>
```