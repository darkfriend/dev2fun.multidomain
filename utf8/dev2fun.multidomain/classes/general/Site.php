<?php
/**
 * @package subdomain
 * @author darkfriend
 * @version 1.0.0
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;


class Site
{
    /** @var array */
    protected static $currentSite;

    /**
     * @param array $filter
     * @return array
     */
    public static function all($filter=[])
    {
        $sites = [];
        $rsSite = \CSite::GetList(
            $by='sort',
            $order='desc',
            $filter
        );

        while ($arSite = $rsSite->Fetch()) {
            $sites[] = $arSite;
        }

        return $sites;
    }

    /**
     * @return string
     * @throws \Bitrix\Main\SystemException
     */
    public static function getCurrent()
    {
        if(self::$currentSite === null) {
            $arSite = \CSite::GetList(
                $by='sort',
                $order='desc',
                [
                    'ABS_DOC_ROOT' => $_SERVER['DOCUMENT_ROOT'],
                ]
            )->Fetch();
            if ($arSite) {
                self::$currentSite = $arSite['LID'];
            } else {
                self::$currentSite = '';
            }
        }

        return self::$currentSite;
    }
}