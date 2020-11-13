<?php

namespace darkfriend\helpers;

/**
 * Class Xml
 * @package darkfriend\helpers
 * @author darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 */
class Xml
{
    protected static $root = '<root/>';

    /**
     * Convert array to xml
     * @param mixed $data
     * @param array $params = [
     *     'root' => '<root/>',
     *     'exception' => false,
     *     'header' => false,
     * ]
     * @return string
     * @throws XmlException
     */
    public static function encode($data, $params = array())
    {
        \libxml_use_internal_errors(true);

        if(empty($params['root'])) {
            $params['root'] = self::$root;
        }
        if(!isset($params['header'])) {
            $params['header'] = false;
        }

        $xml = new SimpleXMLElement(
            self::getHeader($params['header']).$params['root']
        );
        $xml = self::generateXml($xml, $data);

        if(!static::checkException($params)) {
            return '';
        }

        return $xml->asXML();
    }

    /**
     * @param array $params header attributes
     * @return string
     * @since 1.0.1
     */
    public static function getHeader($params = array())
    {
        if(!$params) return '';
        if(!is_array($params)) {
            $params = array();
        }

        $params = array_merge(
            array(
                'version' => '1.0',
                'encoding' => 'utf-8',
            ),
            $params
        );

        $attr = array();
        foreach ($params as $attrKey=>$attrVal) {
            $attr[] = "$attrKey=\"$attrVal\"";
        }

        return '<?xml '.implode(' ', $attr).'?>';
    }

    /**
     * @param SimpleXMLElement $xml
     * @param mixed $data
     * @return SimpleXMLElement
     */
    public static function generateXml($xml, $data)
    {
        /** @var $xml SimpleXMLElement */
        if(is_array($data)) {
            foreach ($data as $key=>$item) {
                self::addChild($xml,$key,$item);
            }
        } else {
            self::addChild($xml,$data);
        }
        return $xml;
    }

    /**
     * Add child
     * @param SimpleXMLElement $xml
     * @param string $name
     * @param array|string $params
     * @return SimpleXMLElement
     */
    public static function addChild($xml, $name, $params = '')
    {
        if(is_array($params)) {
            $value = null;
            if(key_exists('@value',$params)) {
                $value = $params['@value'];
                unset($params['@value']);
            }

            $namespace = null;
            if(key_exists('@namespace',$params)) {
                $namespace = $params['@namespace'];
                unset($params['@namespace']);
            }

            $child = $xml->addChild($name,$value, $namespace);

            if(key_exists('@attributes',$params)) {
                foreach ($params['@attributes'] as $keyAttr=>$attr) {
                    $child->addAttribute($keyAttr, $attr);
                }
                unset($params['@attributes']);
            }

            if(key_exists('@cdata',$params)) {
                $child->addCData($params['@cdata']);
                unset($params['@cdata']);
            }

            foreach ($params as $key => $item) {
                if(is_array($item)) {
                    self::addChild($child,$key,$item);
                } else {
                    $child->addChild($key,$item);
                }
            }
        } else {
            $child = $xml->addChild($name, $params);
        }

        return $child;
    }

    /**
     * Decode XML string
     * @param string $data
     * @param array $params = [
     *     'convert' => true,
     *     'exception' => false,
     * ]
     * @return \darkfriend\helpers\SimpleXMLElement|array
     * @throws XmlException
     */
    public static function decode($data, $params = array())
    {
        \libxml_use_internal_errors(true);

        $xml = \simplexml_load_string(
            $data,
            '\darkfriend\helpers\SimpleXMLElement',
            \LIBXML_NOCDATA
        );

        if(!static::checkException($params)) {
            return array();
        }

        if(!isset($params['convert'])) {
            $params['convert'] = true;
        }

        if($params['convert']) {
            return self::convertSimpleXml($xml);
        } else {
            return $xml;
        }
    }

    /**
     * Convert tree SimpleXMLElement
     * @param SimpleXMLElement $xml
     * @return array
     */
    public static function convertSimpleXml($xml)
    {
        $res = array();
        /** @var SimpleXMLElement $item */
        foreach ($xml as $key=>$item) {
            if($item->count()>0) {
                foreach ($item->children() as $childItem) {
                    $res[$key][] = self::convertSimpleXml($childItem);
                }
            } else {
                $res[$key] = self::convertSimpleXmlItem($item);
            }
        }
        return $res;
    }

    /**
     * Convert item SimpleXMLElement
     * @param SimpleXMLElement $item
     * @return array|string
     */
    public static function convertSimpleXmlItem($item)
    {
        /** @var SimpleXMLElement $item */
        $attr = $item->attributes();
        if(!empty($attr)) {
            $element = (array) $attr;
            $element['@value'] = (string) $item;
        } else {
            $element = (string) $item;
        }
        return $element;
    }

    /**
     * Check error
     * @param array $params = [
     *     'exception' => true,
     * ]
     * @return bool if $params['exception'] === false
     * @throws XmlException if $params['exception'] === true
     */
    public static function checkException($params = array())
    {
        $e = \libxml_get_errors();

        if(!$e) {
            return true;
        }

        $strError = '';
        foreach($e as $key => $xmlError) {
            $strError .= "$key:".$xmlError->message . "\n";
        }

        if($params['exception']) {
            throw new XmlException("XML error: $strError", 100, __FILE__, __LINE__);
        }

        return true;
    }

    /**
     * Set root element
     * @param string $root
     */
    public static function setRootElement($root = '<root/>')
    {
        self::$root = $root;
    }

    /**
     * Get root element
     * @return string
     */
    public static function getRootElement()
    {
        return self::$root;
    }
}