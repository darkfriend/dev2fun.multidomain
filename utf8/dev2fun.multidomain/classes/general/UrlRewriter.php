<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.2.0
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;

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
        if(!self::isPath($uri)) {
            $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId, [
                'QUERY' => $uri,
            ]);
            if($urlRewrites) {
                return current($urlRewrites);
            }
        }
        return [];
    }

    /**
     * @param string $requestUri
     * @return bool
     */
    public static function isPath($requestUri)
    {
        $requestUri = parse_url($requestUri, PHP_URL_PATH);
        $filepath = $_SERVER['DOCUMENT_ROOT'].$requestUri;
        if(!pathinfo($filepath, PATHINFO_EXTENSION) !== 'php') {
            $filepath .= "index.php";
        }
        return is_file($filepath);
    }

    /**
     * Add subdomain rewrite rule
     * @param string $requestUri
     * @param string $siteId
     * @param array $params
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function add($requestUri, $siteId = SITE_ID, $params = [])
    {
        $requestUri = parse_url($requestUri, PHP_URL_PATH);
        $filepath = $_SERVER['DOCUMENT_ROOT'].$requestUri;
        if (is_file($filepath)) {
            if(pathinfo($filepath, PATHINFO_EXTENSION) !== 'php') {
                return false;
            }
            $filename = '';
        } else {
            $filename = 'index.php';
        }

        $arFields = [
            'CONDITION' => $requestUri === '/index.php'
                ? '#^/(?<subdomain>(\\w+))/$#'
                : "#^/(?:/(?<subdomain>\\w+)|){$requestUri}",
//            'CONDITION' => "#^/(?<subdomain>(\w+)){$requestUri}",
            'RULE' => '',
            'ID' => '',
            'PATH' => "{$requestUri}{$filename}",
        ];
        if ($requestUri === '/' ) {
            $arFields['CONDITION'] .= '(?:[\?]+.*|$)#';
        } else {
            $arFields['CONDITION'] .= '#';
        }
        if ($params) {
            foreach ($params as $field => $value) {
                $arFields[$field] = $value;
            }
        }
        \Bitrix\Main\UrlRewriter::add($siteId, $arFields);
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
     * Get all UrlRewrite
     * @param string $siteId
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getAll(string $siteId = SITE_ID): array
    {
        return \Bitrix\Main\UrlRewriter::getList($siteId);
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
                "/(?:/(?<subdomain>\\w+)|)/$1/",
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

    /**
     * @param string $path
     * @param array $dumpRewrites
     * @return array
     */
    public static function getDumpRewriteByPath(string $path, array $dumpRewrites): array {
        $filtered = array_filter(
            $dumpRewrites,
            function ($urlRewrite) use ($path) {
                return $path === $urlRewrite['PATH'];
            }
        );

        return $filtered ? current($filtered) : [];
    }

    /**
     * @param string $siteId
     * @param array|null $dumpRewrites
     * @return void
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function restore(string $siteId = SITE_ID, ?array $dumpRewrites = null)
    {
        if ($dumpRewrites === null) {
            $option = Option::get(
                \Dev2fun\MultiDomain\Base::$module_id,
                'dump_url_rewrite'
            );
            $dumpRewrites = json_decode($option, true);
        }
        $urlRewrites = self::getAll($siteId);
        if (!$urlRewrites) {
            return;
        }

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
            $dumpRewrite = static::getDumpRewriteByPath($urlRewrite['PATH'], $dumpRewrites);
            if ($dumpRewrite) {
                \Bitrix\Main\UrlRewriter::add($siteId, $dumpRewrite);
            }
        }
    }
}
