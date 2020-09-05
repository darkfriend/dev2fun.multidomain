<?php

namespace darkfriend\helpers;

/**
 * Class Json
 * @package darkfriend\helpers
 * @author darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 */
class Json
{
    /**
     * Json encode
     * @param mixed $data
     * @param array $params
     * @return array
     * @throws JsonException
     */
    public static function encode($data, $params = array())
    {
        if (empty($params['options'])) {
            $params['options'] = \JSON_HEX_TAG|\JSON_HEX_AMP|\JSON_HEX_APOS|\JSON_HEX_QUOT;
        }

        $res = \json_encode($data, $params['options']);

        if(!static::checkException($params)) {
            return $res;
        }

        return $res;
    }

    /**
     * @param string $data
     * @param array $params
     * @return array
     * @throws JsonException
     */
    public static function decode($data, $params = array())
    {
        $res = \json_decode($data, true);

        if(!static::checkException()) {
            return $res;
        }

        return $res;
    }

    /**
     * @param array $params = [
     *     'options' => 0,
     *     'exception' => false,
     * ]
     * @return bool
     * @throws JsonException
     */
    protected static function checkException($params = array())
    {
        $e = \json_last_error();

        if ($e == \JSON_ERROR_NONE) {
            return false;
        }

        if (empty($params['options'])) {
            $params['options'] = 0;
        }

        if ($e == \JSON_ERROR_UTF8 && ($params['options'] & \JSON_PARTIAL_OUTPUT_ON_ERROR)) {
            return false;
        }

        if($params['exception']) {
            if (function_exists('json_last_error_msg')) {
                // Must be available on PHP >= 5.5.0
                $message = sprintf('%s [%d]', \json_last_error_msg(), $e);
            } else {
                $message = $e;
            }
            throw new JsonException("JSON error: $message", 100, __FILE__, __LINE__);
        }

        return true;
    }
}