<?php

namespace darkfriend\helpers;

/**
 * This helper methods for string
 * @package darkfriend\helpers
 * @author darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 */
class StringHelper
{
    /**
     * Get encoded string
     *
     * @param mixed $val
     * @return array|string
     */
    public static function htmlspecialchars($val)
    {
        if (\is_array($val)) {
            $arrReturn = array();
            foreach ($val as $key => $value) {
                $arrReturn[$key] = self::htmlspecialchars($value);
            }
            return $arrReturn;
        } else {
            if (!empty($val)) {
                return \htmlspecialchars(
                    $val,
                    \ENT_QUOTES | \ENT_HTML5 | \ENT_DISALLOWED | \ENT_SUBSTITUTE,
                    'UTF-8'
                );
            }
        }
        return '';
    }

    /**
     * Get decoded string
     *
     * @param mixed $val
     * @return array|string
     */
    public static function htmlspecialchars_decode($val)
    {
        if (\is_array($val)) {
            $arrReturn = array();
            foreach ($val as $key => $value) {
                $arrReturn[$key] = self::htmlspecialchars_decode($value);
            }
            return $arrReturn;
        } else {
            if ($val) {
                return \htmlspecialchars_decode(
                    $val,
                    \ENT_QUOTES | \ENT_DISALLOWED | \ENT_SUBSTITUTE
                );
            }
        }
        return '';
    }

    /**
     * Get generated string
     *
     * @param int $length
     * @param string $chars
     * @return string
     */
    public static function generateString($length = 8, $chars = '0123456789ABDEFGHKNQRSTYZ')
    {
        $numChars = \strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= \substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }

    /**
     * Return suffix word
     * @param int $value число
     * @param array $words массив возможных окончаний
     * @return string
     * @example 5 товаров, 1 товар, 3 товара
     */
    public static function getDeclension($value = 1, $words = array('', 'а', 'ов'))
    {
        $array = array(2, 0, 1, 1, 1, 2);

        return $words[($value % 100 > 4 && $value % 100 < 20) ? 2 : $array[($value % 10 < 5) ? $value % 10 : 5]];
    }

    /**
     * Return truncated string to the number of characters specified.
     *
     * @param string $string The string to truncate.
     * @param int $length How many characters from original string to include into truncated string.
     * @param string $suffix String to append to the end of truncated string.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string the truncated string.
     */
    public static function truncate($string, $length, $suffix = '...', $encoding = null)
    {
        if ($encoding === null) {
            $encoding = 'UTF-8';
        }

        if (\mb_strlen($string, $encoding) > $length) {
            return \rtrim(\mb_substr($string, 0, $length, $encoding)) . $suffix;
        }

        return $string;
    }

    /**
     * Return truncated string to the number of words specified.
     *
     * @param string $string The string to truncate.
     * @param int $count How many words from original string to include into truncated string.
     * @param string $suffix String to append to the end of truncated string.
     * @return string the truncated string.
     */
    public static function truncateWords($string, $count, $suffix = '...')
    {
        $words = \preg_split('/(\s+)/u', \trim($string), null, \PREG_SPLIT_DELIM_CAPTURE);
        if (\count($words) / 2 > $count) {
            return \implode('', \array_slice($words, 0, ($count * 2) - 1)) . $suffix;
        }

        return $string;
    }

    /**
     * Get translit string
     * @param string $str
     * @param array $params iso key - array and lower key - boolean
     * @return string
     */
    public static function translit($str, $params = array())
    {
        if(!$str) return $str;
        if(empty($params['iso'])) {
            $iso = array(
                "Є"=>"YE","І"=>"I","Ѓ"=>"G","і"=>"i","№"=>"","є"=>"ye","ѓ"=>"g",
                "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
                "Е"=>"E","Ё"=>"YO","Ж"=>"ZH",
                "З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L",
                "М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
                "С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"X",
                "Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'",
                "Ы"=>"Y","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA",
                "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
                "е"=>"e","ё"=>"yo","ж"=>"zh",
                "з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
                "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
                "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"x",
                "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
                "ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
                "«"=>"","»"=>"","—"=>"-"," "=>"",
            );
        }
        if(!isset($params['lower'])) {
            $params['lower'] = true;
        }
        return $params['lower'] ? \strtolower(\strtr($str, $params['iso'])) : \strtr($str, $params['iso']);
    }

    /**
     * Add slashes in string
     * @param string|string[] $str
     * @param array $params
     * @return string|string[]
     */
    public static function addSlashes($str, $params = array())
    {
        if(!$str) return $str;
        if(!isset($params['search'])) {
            $params['search'] = array("\\", "\"", "'");
        }
        if(!isset($params['replace'])) {
            $params['replace'] = array("\\\\", '\\"', "\\'");
        }
        return \str_replace($params['search'], $params['replace'], $str);
    }
}