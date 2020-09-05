<?php

namespace darkfriend\helpers;

/**
 * Class Response
 * @package darkfriend\helpers
 * @author darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 */
class Response
{
    /**
     * @param array $data
     * @param array $params = [
     *     'show' => false,
     *     'die' => false,
     * ]
     * @return array
     * @throws JsonException
     */
    public static function json($data, $params = array())
    {
        header('Content-Type: application/json');

        if(!is_string($data)) {
            $data = Json::encode($data, $params);
        }

        if(isset($params['show'])) {
            echo $data;
        }

        if(isset($params['die'])) {
            die();
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $params
     * @return string
     * @throws XmlException
     */
    public static function xml($data, $params = array())
    {
        header('Content-Type: text/xml');

        if(!is_string($data)) {
            $data = Xml::encode($data, $params);
        }

        if(isset($params['show'])) {
            echo $data;
        }

        if(isset($params['die'])) {
            die();
        }

        return $data;
    }

    /**
     * @param array $headers
     */
    public static function setHeader($headers)
    {
        if($headers) {
            foreach ($headers as $key=>$header) {
                header("$key: $header");
            }
        }
    }
}