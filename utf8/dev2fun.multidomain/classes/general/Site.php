<?php
/**
 * @author darkfriend
 * @version 1.1.10
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;


class Site
{
    /** @var array */
    protected static $currentSite;
    /** @var string */
    protected static $defaultSite;

    /**
     * @param array $filter
     * @return array
     */
    public static function all($filter=[])
    {
        $sites = [];
        $rsSite = \CSite::GetList(
            $by='sort',
            $order='asc',
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
                'sort',
                'desc',
                [
                    'IN_DIR' => $_SERVER['DOCUMENT_ROOT'],
                    'DOMAIN' => $_SERVER['HTTP_HOST'],
                    'ACTIVE' => 'Y',
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

    /**
     * @return string
     * @throws \Bitrix\Main\SystemException
     */
    public static function getDefault()
    {
        if(self::$defaultSite === null) {
            $currentSite = current(
                array_filter(
                    static::all(['DEF'=>'Y']),
                    function($item) {
                        return $item['DEF'] === 'Y';
                    }
                )
            );

            self::$defaultSite = isset($currentSite['LID']) ? $currentSite['LID'] : '';
        }

        return self::$defaultSite;
    }
}