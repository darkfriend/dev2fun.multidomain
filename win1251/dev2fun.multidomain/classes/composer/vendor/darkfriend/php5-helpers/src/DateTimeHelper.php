<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 23.02.2020
 * Time: 23:05
 */

namespace darkfriend\helpers;

/**
 * Class DateTimeHelper
 * @package darkfriend\helpers
 */
class DateTimeHelper
{
    /**
     * Return amount seconds before end day
     * @return int amount seconds
     */
    public static function getAmountEndDay()
    {
        return static::getAmountSeconds(strtotime("tomorrow") - 1);
    }

    /**
     * Return amount seconds before $endTime
     * @param mixed $endTime
     * @return int amount seconds
     */
    public static function getAmountSeconds($endTime)
    {
        if(!is_numeric($endTime)) {
            $endTime = strtotime($endTime);
        }
        return $endTime-strtotime('now');
    }
}