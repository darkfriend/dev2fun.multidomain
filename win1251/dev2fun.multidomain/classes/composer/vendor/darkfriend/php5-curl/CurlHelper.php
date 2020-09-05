<?php

namespace darkfriend\helpers;

/**
 * CurlHelper - php5 curl helper package
 * @package darkfriend\helpers
 * @version 1.0.4
 * @author darkfriend <hi@darkfriend.ru>
 */
class CurlHelper
{
    /** @var self */
    protected static $_instance;
    /** @var resource */
    protected $_ch;

    /** @var array */
    protected $headers = array();
    /** @var int */
    protected $timeout = 60;
    /** @var bool */
    protected $debug = false;
    /** @var string */
    protected $debugFile = '';
    /** @var int  */
    protected $port;
    /** @var array */
    protected $curlProperties;
    /** @var int */
    protected $jsonEncodeOptions = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES;

    /** @var int */
    public $lastCode = 0;
    /** @var string */
    public $lastHeaders = '';
    /** @var string */
    public $lastError;
    /** @var string */
    public $requestHeaders = '';
    /** @var array */
    public $requestInfo;

    /** @var string */
    public $acceptLanguage = 'ru-RU';

    /**
     * Singleton
     * @param bool $newSession create new instance
     * @param array $options
     * @return self
     * @throws \Exception
     */
    public static function getInstance($newSession = false, $options = array())
    {
        if (!\function_exists('curl_init')) {
            throw new \Exception('Curl is not found!');
        }
        if (!self::$_instance || $newSession) {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }

    /**
     * CurlHelper constructor.
     * @param array $options
     */
    protected function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Set options for
     * @param array $options
     * @return $this
     */
    public function setOptions($options = array())
    {
        if(!$options) return $this;
        foreach ($options as $key => $option) {
            if (\substr($key, 0, 1) == '_') continue;
            if (isset($this->{$key})) {
                $this->{$key} = $option;
            }
        }
        return $this;
    }

    /**
     * Add http headers
     * @param array $headers key=>$value
     * @return $this
     * @example [[$headers]] ['Accept-Language'=>'en-US']
     * @deprecated
     * @uses setHeaders()
     */
    public function addHeaders(array $headers)
    {
        return $this->setHeaders($headers);
    }

    /**
     * Set http headers
     * @param array $headers key=>$value
     * @return $this
     * @example [[$headers]] ['Accept-Language'=>'en-US']
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set request port
     * @param int $port
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set curl properties
     * @param array $properties
     * @return $this
     */
    public function setCurlProperties($properties)
    {
        if(!$properties) return $this;
        $this->curlProperties = \array_merge(
            $this->curlProperties,
            $properties
        );
        return $this;
    }

    /**
     * Get curl properties
     * @return array
     */
    public function getCurlProperties()
    {
        return $this->curlProperties;
    }

    /**
     * Cleared curl properties
     * @return $this
     */
    public function clearCurlProperties()
    {
        $this->curlProperties = array();
        return $this;
    }

    /**
     * @param array $data
     * @param int $options
     * @return string
     * @since 1.0.2
     */
    public static function getRequestJson($data, $options = 0)
    {
        return \json_encode($data, $options);
    }

    /**
     * Array to XML encode
     * @param array $data
     * @return string
     * @throws XmlException
     * @since 1.0.5
     */
    public static function getRequestXml($data)
    {
        return Xml::encode($data);
    }

    /**
     * Set headers to curl
     * @return $this
     */
    protected function initHeaders()
    {
        if (empty($this->headers['Accept-Language'])) {
            $this->headers['Accept-Language'] = $this->acceptLanguage;
        }
        $headers = array();
        foreach ($this->headers as $key => $header) {
            if ($header) {
                $headers[] = "$key: $header";
            }
        }
        \curl_setopt(
            $this->_ch,
            \CURLOPT_HTTPHEADER,
            $headers
        );
        return $this;
    }

    /**
     * Return curl-resource and set headers
     * @param string $method http-method
     * @return false|resource
     * @throws \Exception
     */
    protected function initCurl($method = 'post')
    {
        if (!\function_exists('curl_init')) {
            throw new \Exception('curl is not found!');
        }
        if (empty($this->_ch)) {
            $this->_ch = \curl_init();
            \curl_setopt($this->_ch, \CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($this->_ch, \CURLOPT_FOLLOWLOCATION, true);
            if($this->port) {
                \curl_setopt($this->_ch, \CURLOPT_PORT, $this->port);
            }
            switch ($method) {
                case 'get': break;
                case 'post':
                    \curl_setopt($this->_ch, \CURLOPT_POST, 1);
                    break;
                default:
                    \curl_setopt($this->_ch, \CURLOPT_CUSTOMREQUEST, $method);
                    break;
            }
            \curl_setopt($this->_ch, \CURLOPT_HEADER, 1);
            \curl_setopt($this->_ch, \CURLINFO_HEADER_OUT, 1);
            \curl_setopt($this->_ch, \CURLOPT_SSL_VERIFYPEER, 0);
            \curl_setopt($this->_ch, \CURLOPT_TIMEOUT, $this->timeout);
            \curl_setopt($this->_ch, \CURLOPT_CONNECTTIMEOUT, $this->timeout);
        }
        if($this->curlProperties) {
            \curl_setopt_array($this->_ch, $this->curlProperties);
        }
        return $this->_ch;
    }

    /**
     * Do request
     * @param string $url
     * @param array $data request data
     * @param string $method request method (post)
     * @param string $requestType request content-type (text)
     * @param string $responseType response content-type (json)
     * @return mixed
     * @throws \Exception
     */
    public function request($url, $data = array(), $method = 'post', $requestType = '', $responseType = 'json')
    {
        $this->clear();
        $this->initCurl($method);
        if($data) {
            switch ($requestType) {
                case 'json':
                    $data = static::getRequestJson($data, $this->jsonEncodeOptions);
                    break;
                case 'xml':
                    $data = static::getRequestXml($data);
                    break;
                default:
                    $data = \http_build_query($data);
            }
            if($method == 'get') {
                $url .= '?' . $data;
            } else {
                \curl_setopt($this->_ch, \CURLOPT_POSTFIELDS, $data);
            }
        }
        \curl_setopt($this->_ch, \CURLOPT_URL, $url);

        if($requestType) {
            switch ($requestType) {
                case 'json':
                    $this->headers['Content-Type'] = 'application/json; charset=utf-8';
                    break;
                case 'xml':
                    $this->headers['Content-Type'] = 'text/xml';
                    break;
                default:
                    $this->headers['Content-Type'] = "$requestType; charset=utf-8";
            }
        }

        $this->initHeaders();

        if ($this->debug) {
            $this->debug(array(
                'debugEvent' => 'before-request',
                'url' => $url,
                'requestHeaders' => $this->requestHeaders,
                'requestData' => $data,
                'requestType' => $requestType,
                'responseType' => $responseType,
            ));
        }

        $response = \curl_exec($this->_ch);
        $this->requestInfo = \curl_getinfo($this->_ch);

        $header_size = \curl_getinfo($this->_ch, \CURLINFO_HEADER_SIZE);
        $this->lastHeaders = \substr($response, 0, $header_size);

        $this->lastCode = (int) \curl_getinfo($this->_ch, \CURLINFO_HTTP_CODE);
        $this->requestHeaders = \curl_getinfo($this->_ch, \CURLINFO_HEADER_OUT);

        \curl_close($this->_ch);

        $body = \substr($response, $header_size);

        if ($this->debug) {
            $this->debug(array(
                'debugEvent' => 'after-request',
                'url' => $url,
                'requestHeaders' => $this->requestHeaders,
                'requestData' => $data,
                'code' => $this->lastCode,
                'responseHeaders' => $this->lastHeaders,
                'requestType' => $requestType,
                'responseType' => $responseType,
                'body' => $body,
            ));
        }

        $body = trim($body);
        if(strlen($body)>0) {
            switch ($responseType) {
                case 'json':
                    $body = \json_decode($body, true);
                    break;
                case 'xml':
                    $body = Xml::decode($body);
                    break;
            }
        }

        if ($this->debug) {
            $this->debug(array(
                'debugEvent' => 'result',
                'body' => $body,
            ));
        }

        return $body;
    }

    /**
     * Check cookie file
     * @param string $cookieFile
     * @return bool
     */
    public function checkCookie($cookieFile='cookies.txt')
    {
        $result = false;
        if (file_exists($cookieFile)) {
            $result = true;
        } else {
            $r = @fopen($cookieFile, 'w');
            if($r) {
                $result = true;
                fclose($r);
            }
        }
        return $result;
    }

    /**
     * Clear last request
     */
    protected function clear()
    {
        $this->lastCode = 0;
        $this->lastError = '';
        $this->lastHeaders = '';
        unset($this->_ch);
    }

    /**
     * Debug
     * @param mixed $data
     * @return void
     */
    protected function debug($data)
    {
        DebugHelper::traceInit('curl', DebugHelper::TRACE_MODE_APPEND, $this->debugFile);
        DebugHelper::trace($data);
    }
}