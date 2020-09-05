<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 23.02.2020
 * Time: 17:49
 */

namespace darkfriend\helpers;

/**
 * Class FileHelper
 * @package darkfriend\helpers
 */
class FileHelper
{
    /**
     * Return base64 code for file
     * @param string $path absolute path to file
     * @param null|string $type mime-type file or null
     * @return string
     * @example return data:image/png;base64,<data>
     */
    public static function getBase64($path, $type = null)
    {
        if(!$type) {
            $type = \mime_content_type($path);
        }
        $data = \file_get_contents($path);
        return 'data:' . $type . ';base64,' . \base64_encode($data);
    }
}