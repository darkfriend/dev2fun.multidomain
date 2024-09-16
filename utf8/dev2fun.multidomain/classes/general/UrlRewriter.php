<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.2.2
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;


use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
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
        if (self::$current === null) {
            self::$current = self::getRouteByUri($GLOBALS['APPLICATION']->GetCurUri(), $siteId);
        }
        return self::$current;
    }

    /**
     * Return route by path
     * @param string $path
     * @param string $siteId
     * @return array|false
     */
    public static function getByPath(string $path, string $siteId = SITE_ID)
    {
        $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId, [
            'PATH' => $path,
        ]);
        return current($urlRewrites);
    }

    /**
     * @param string $uri
     * @param string $siteId
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getRouteByUri($uri, $siteId = SITE_ID)
    {
        if (!self::isPath($uri)) {
            $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId, [
                'QUERY' => $uri,
            ]);
            if ($urlRewrites) {
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
        $filepath = $_SERVER['DOCUMENT_ROOT'] . $requestUri;
        if (!pathinfo($filepath, PATHINFO_EXTENSION) !== 'php') {
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
        $filepath = $_SERVER['DOCUMENT_ROOT'] . $requestUri;
        if (is_file($filepath)) {
            if (pathinfo($filepath, PATHINFO_EXTENSION) !== 'php') {
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
        if ($requestUri === '/') {
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
        if (is_string($urlRewrite)) {
            $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId, [
                'QUERY' => $urlRewrite,
            ]);
            if ($urlRewrites) {
                $urlRewrite = current($urlRewrites);
            }
        }

        if ($urlRewrite) {
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
    public static function setAll(string $siteId = SITE_ID)
    {
        $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId);
        if (!$urlRewrites) {
            return null;
        }

        foreach ($urlRewrites as $urlRewrite) {
            if (strpos($urlRewrite['CONDITION'], '?<subdomain>') !== false) {
                continue;
            }
            if (empty($urlRewrite['ID'])) {
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
     * @param string $siteId
     * @param string $path
     * @param array|null $urlRewrites
     * @return void
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function updateSubdomain(string $siteId = SITE_ID, string $path = '', ?array $urlRewrites = null): void
    {
        if (!$urlRewrites) {
            $urlRewrites = \Bitrix\Main\UrlRewriter::getList($siteId);
        }
        if (!$urlRewrites) {
            return;
        }
        $urlRewritesFiltered = array_filter($urlRewrites, function ($urlItem) use ($path)
        {
            return $path === $urlItem['PATH'];
        });

        foreach ($urlRewritesFiltered as $urlRewrite) {
            if (strpos($urlRewrite['CONDITION'], '?<subdomain>') !== false) {
                continue;
            }
            $filterCondition = $urlRewrite['CONDITION'];
            $urlRewrite['CONDITION'] = preg_replace(
                '#/(.*)/#',
                "(?:/(?<subdomain>\\w+)|)/$1/",
                $urlRewrite['CONDITION']
            );
            \Bitrix\Main\UrlRewriter::update(
                $siteId,
                [
                    'CONDITION' => $filterCondition,
                ],
                $urlRewrite
            );
        }
    }

    /**
     * Add support index.php page
     * @param string $siteId
     * @return void
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function addIndexSubdomain(string $siteId = SITE_ID): void
    {
        $urlRewrite = [
            'CONDITION' => '#^/(?<subdomain>(\\w+))/$#',
            'PATH' => '/index.php',
            'SORT' => 100,
        ];
        \Bitrix\Main\UrlRewriter::add($siteId, $urlRewrite);
    }

    /**
     * Add support another index pages
     * @param string $siteId
     * @return void
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function addPagesSubdomain(string $siteId = SITE_ID): void
    {
        $urlRewrite = [
            'CONDITION' => '#^(?:/(?<subdomain>\\w+)|)/(.*[\/])#',
            'RULE' => '/$2/index.php',
            'SORT' => 100,
        ];
        \Bitrix\Main\UrlRewriter::add($siteId, $urlRewrite);
    }

    /**
     * Add support all custom scripts
     * @param string $siteId
     * @return void
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function addScriptsSubdomain(string $siteId = SITE_ID): void
    {
        $urlRewrite = [
            'CONDITION' => '#^(?:\/(?<subdomain>\\w+)|)\/(.*[\/])(\\w+\.\\w+)(\\?.*)?#',
            'RULE' => '/$2/$3',
            'SORT' => 100,
        ];
        \Bitrix\Main\UrlRewriter::add($siteId, $urlRewrite);
    }

    /**
     * Remove subdomain for all rules
     * @param string $siteId
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function removeAll(string $siteId = SITE_ID)
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

    /**
     * @param string $siteId
     * @param string $path
     * @param array|null $dumpRewrites
     * @return void
     * @throws ArgumentNullException
     */
    public static function restoreByPath(string $siteId, string $path, ?array $dumpRewrites = null): void
    {
        if ($dumpRewrites === null) {
            $option = Option::get(
                \Dev2fun\MultiDomain\Base::$module_id,
                'dump_url_rewrite'
            );
            $dumpRewrites = json_decode($option, true);
        }

        foreach ($dumpRewrites as $urlRewrite) {
            if ($urlRewrite['PATH'] !== $path) {
                continue;
            }
            \Bitrix\Main\UrlRewriter::update(
                $siteId,
                [
                    'PATH' => $path,
                ],
                $urlRewrite
            );
        }
    }

    /**
     * Remove urlrewrite by filter
     * @param string $siteId
     * @param array $filter
     * @return void
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function removeByFilter(string $siteId, array $filter): void
    {
        if (isset($filter['RULE'])) {
            BitrixUrlRewriter::delete($siteId, $filter);
        } else {
            \Bitrix\Main\UrlRewriter::delete($siteId, $filter);
        }
    }
}
