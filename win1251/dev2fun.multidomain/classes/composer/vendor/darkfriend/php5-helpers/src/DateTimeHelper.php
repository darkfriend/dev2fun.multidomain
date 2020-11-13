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
 * @version 1.0.4
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

    /**
     * Get age
     * @param string|\DateTime $date1
     * @param string|\DateTime $date2
     * @return int
     * @throws \Exception
     * @since 1.0.4
     */
    public static function getAge($date1, $date2 = 'now')
    {
        if(!($date1 instanceof \DateTime)) {
            $date1 = new \DateTime($date1);
        }
        if(!($date2 instanceof \DateTime)) {
            $date2 = new \DateTime($date2);
        }
        return (int) $date1->diff($date2)->y;
    }
}