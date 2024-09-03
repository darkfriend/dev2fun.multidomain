<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.2.0
 * @since 1.2.0
 */

namespace Dev2fun\MultiDomain;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;

class BitrixUrlRewriter extends \Bitrix\Main\UrlRewriter
{
    /**
     * @param array $arUrlRewrite
     * @param array $arFilter
     * @return array
     * @throws ArgumentException
     */
    protected static function filterRules(array $arUrlRewrite, array $arFilter)
    {
        $arResultKeys = array();

        foreach ($arUrlRewrite as $keyRule => $arRule)
        {
            $isMatched = true;
            foreach ($arFilter as $keyFilter => $valueFilter)
            {
                $isNegative = false;
                if (mb_substr($keyFilter, 0, 1) == "!")
                {
                    $isNegative = true;
                    $keyFilter = mb_substr($keyFilter, 1);
                }

                if ($keyFilter == 'QUERY')
                    $isMatchedTmp = preg_match($arRule["CONDITION"], $valueFilter);
                elseif ($keyFilter == 'CONDITION')
                    $isMatchedTmp = ($arRule["CONDITION"] == $valueFilter);
                elseif ($keyFilter == 'RULE')
                    $isMatchedTmp = ($arRule["RULE"] == $valueFilter);
                elseif ($keyFilter == 'ID')
                    $isMatchedTmp = ($arRule["ID"] == $valueFilter);
                elseif ($keyFilter == 'PATH')
                    $isMatchedTmp = ($arRule["PATH"] == $valueFilter);
                else
                    throw new ArgumentException("arFilter");

                $isMatched = ($isNegative xor $isMatchedTmp);

                if (!$isMatched)
                    break;
            }

            if ($isMatched)
                $arResultKeys[] = $keyRule;
        }

        return $arResultKeys;
    }

    /**
     * @param string $siteId
     * @param array $arFilter
     * @return void
     * @throws ArgumentException
     * @throws ArgumentNullException
     */
    public static function delete($siteId, $arFilter)
    {
        if (empty($siteId)) {
            throw new ArgumentNullException("siteId");
        }

        $arUrlRewrite = static::loadRules($siteId);

        $arResultKeys = static::filterRules($arUrlRewrite, $arFilter);
        foreach ($arResultKeys as $key) {
            unset($arUrlRewrite[$key]);
        }

        uasort($arUrlRewrite, array('\Bitrix\Main\UrlRewriter', "recordsCompare"));

        static::saveRules($siteId, $arUrlRewrite);
        Application::resetAccelerator();
    }
}