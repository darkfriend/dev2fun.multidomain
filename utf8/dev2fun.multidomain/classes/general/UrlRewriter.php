<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;


class UrlRewriter
{
    public static $current;

    /**
     * Get current route
     * @param string $siteId
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getCurrent($siteId = SITE_ID)
    {
        if(self::$current === null) {
            self::$current = self::getRouteByUri($GLOBALS['APPLICATION']->GetCurUri(), $siteId);
        }
        return self::$current;
    }

    /**
     * @param string $uri
     * @param string $siteId
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getRouteByUri($uri, $siteId = SITE_ID)
    {
        $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId, [
            'QUERY' => $uri,
        ]);
        if($urlRewrites) {
            return current($urlRewrites);
        }
        return [];
    }

    /**
     * Add subdomain rewrite rule
     * @param string $requestUri
     * @param string $siteId
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function add($requestUri, $siteId = SITE_ID)
    {
        $requestUri = parse_url($requestUri, PHP_URL_PATH);
        $filepath = $_SERVER['DOCUMENT_ROOT'].$requestUri;
        if(is_file($filepath)) {
            if(pathinfo($filepath, PATHINFO_EXTENSION) !== 'php') {
                return false;
            } else {
                $filename = '';
            }
        } else {
            $filename = 'index.php';
        }

        \Bitrix\Main\UrlRewriter::add(
            $siteId,
            [
                'CONDITION' => "#^/(?<subdomain>(\w+)){$requestUri}#",
                'RULE' => '',
                'ID' => '',
                'PATH' => "{$requestUri}{$filename}",
            ]
        );
        return true;
    }

    /**
     * Update subdomain rewrite rule
     * @param array|string $urlRewrite
     * @param string $siteId
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function update($urlRewrite, $siteId = SITE_ID)
    {
        if(is_string($urlRewrite)) {
            $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId, [
                'QUERY' => $urlRewrite,
            ]);
            if($urlRewrites) {
                $urlRewrite = current($urlRewrites);
            }
        }

        if($urlRewrite) {
            $newCondition = preg_replace(
                '#/(.*)/#',
                "/(?<subdomain>(\w+))/$1/",
                $urlRewrite['CONDITION']
            );
            \Bitrix\Main\UrlRewriter::update(
                $siteId,
                [
                    'CONDITION' => $urlRewrite['CONDITION'],
                ],
                [
                    'CONDITION' => $newCondition,
                ]
            );
        }
    }

    /**
     * Check condition has subdomain
     * @param string $condition
     * @return bool
     */
    public static function hasSubdomainCondition($condition)
    {
        return strpos($condition, '?<subdomain>') !== false;
    }

    /**
     * Set subdomain for all rules
     * @param string $siteId
     * @return void|null
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function setAll($siteId = SITE_ID)
    {
        $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId);
        if(!$urlRewrites) {
            return null;
        }
        foreach ($urlRewrites as $urlRewrite) {
            if (strpos($urlRewrite['CONDITION'], '?<subdomain>') !== false) {
                continue;
            }
            if(empty($urlRewrite['ID'])) {
                continue;
            }
            $urlRewrite['CONDITION'] = preg_replace(
                '#/(.*)/#',
                "/(?<subdomain>(\w+))/$1/",
                $urlRewrite['CONDITION']
            );
            \Bitrix\Main\UrlRewriter::add($siteId, $urlRewrite);
        }
    }

    /**
     * Remove subdomain for all rules
     * @param string $siteId
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function removeAll($siteId = SITE_ID)
    {
        $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId);
        if(!$urlRewrites) {
            foreach ($urlRewrites as $urlRewrite) {
                if (strpos($urlRewrite['CONDITION'], '?<subdomain>') === false) {
                    continue;
                }
                \Bitrix\Main\UrlRewriter::delete(
                    $siteId,
                    [
                        'CONDITION' => $urlRewrite['CONDITION'],
                    ]
                );
            }
        }
    }

}
