<?php

namespace darkfriend\helpers;

/**
 * Class Request
 * @package darkfriend\helpers
 * @author darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 */
class Request
{
    /**
     * @return string
     */
    public static function getBody()
    {
        return trim(file_get_contents('php://input'));
    }

    /**
     * Get json from request body
     * @param array $params
     * @return array|string
     * @throws JsonException
     */
    public static function getBodyJson($params)
    {
        $input = self::getBody();
        if($input) {
            $input = Json::decode($input, $params);
        }
        return $input;
    }

    /**
     * Get xml from request body
     * @param array $params = [
     *     'decode' => true,
     * ]
     * @return array|SimpleXMLElement|string
     * @throws XmlException
     */
    public static function getBodyXml($params)
    {
        $input = self::getBody();

        if(!isset($params['decode'])) {
            $params['decode'] = true;
        }

        if($input && $params['decode']) {
            $input = Xml::decode($input, $params);
        }

        return $input;
    }

    /**
     * Get $_POST
     * @param null|string $param
     * @param string $default
     * @param bool $strict
     * @return bool|int|mixed|string|null
     */
    public static function post($param = null, $default = '', $strict = true)
    {
        if(!$param) {
            return $_POST;
        }

        if($strict && !isset($_POST[$param])) {
            return TypeHelper::toStrict($default);
        } elseif(!$strict && empty($_POST[$param])) {
            return $default;
        }

        if($strict) {
            return TypeHelper::toStrict($_POST[$param]);
        } else {
            return $_POST[$param];
        }
    }

    /**
     * Get request $_GET
     * @param null|string $param
     * @param string $default
     * @param bool $strict
     * @return bool|int|mixed|string|null
     */
    public static function get($param = null, $default = '', $strict = true)
    {
        if(!$param) {
            return $_GET;
        }

        if($strict && !isset($_GET[$param])) {
            return TypeHelper::toStrict($default);
        } elseif(!$strict && empty($_GET[$param])) {
            return $default;
        }

        if($strict) {
            return TypeHelper::toStrict($_GET[$param]);
        } else {
            return $_POST[$param];
        }
    }
}