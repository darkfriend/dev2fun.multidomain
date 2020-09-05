<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 05.01.2020
 * Time: 17:08
 */

namespace darkfriend\helpers;


class Log
{
    /**
     * @param null|string $sessionHash
     * @param null|integer $mode
     * @param string $pathLog
     */
    public static function init($sessionHash = null, $mode = null, $pathLog = '/')
    {
        DebugHelper::traceInit($sessionHash, $mode, $pathLog);
    }

    /**
     * Save trace message
     * @param mixed $message
     * @param string $category
     * @return void
     */
    public static function add($message, $category = 'common')
    {
        DebugHelper::trace($message, $category);
    }
}