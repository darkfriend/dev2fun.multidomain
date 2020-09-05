# CurlHelper - хелпер для удобной работы с CURL

* ```CurlHelper::getInstance($newSession = false, $options = [])``` - return instance CurlHelper
* ```CurlHelper::getInstance($newSession = false, $options = [])->request($url, $data = [], $method = 'post', $requestType = '', $responseType = 'json')``` - do request to url

### Support request method type
* post
* get
* put
* delete
* options
* custom

### Support request type
* json
* xml
* custom

### Support response type
* json
* xml
* custom

## Example1

```php
$url = 'http://site.ru';
$curl = \darkfriend\helpers\CurlHelper::getInstance();
$response = $curl->request($url);

// $response - array response site.ru
// $curl->lastCode - response http code
// $curl->lastHeaders - response http headers
```

## Example2: CurlHelper with custom headers

```php
$url = 'http://site.ru';
$curl = \darkfriend\helpers\CurlHelper::getInstance();
$response = $curl->setHeaders([
            'Accept-Language' => 'ru-RU',
            'Custom-Head' => 'custom',
        ])
        ->request($url);

// $response - array response site.ru
// $curl->lastCode - response http code
// $curl->lastHeaders - response http headers
```

## Example3: CurlHelper with debug

```php
$url = 'http://site.ru';
$curl = \darkfriend\helpers\CurlHelper::getInstance(false,[
    'debug' => true,
    'debugFile' => __DIR__.'/logs'
]);
$response = $curl->request($url);

// $response - array response site.ru
// $curl->lastCode - response http code
// $curl->lastHeaders - response http headers
```