<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.2.2
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Context;

class LinkReplace
{
    /**
     * @var string[]
     */
    protected static $links;

    /**
     * @param string $content
     * @param array|null $currentSubDomain
     * @return string
     */
    public static function process($content, $currentSubDomain=null)
    {
        if(!$currentSubDomain) {
            $currentSubDomain = Base::GetCurrentDomain();
        }
        if($currentSubDomain['UF_SUBDOMAIN'] === 'main') {
            return $content;
        }
        static::$links = array_unique(
            array_merge(
                static::findLinks($content),
                static::findFormActions($content)
            )
        );
        $linkReplacer = [];
        if (self::$links) {
            foreach (self::$links as $link) {
                $linkDomain = self::getReplacePathForce($link, $currentSubDomain);
                if ($linkDomain === $link) {
                    continue;
                }
                $linkReplacer["#href=\"($link)\"#ium"] = "href=\"{$linkDomain}\"";
            }
            $content = preg_replace(
                array_keys($linkReplacer),
                array_values($linkReplacer),
                $content
            );
        }
        return $content;
    }

    /**
     * @param string $uri
     * @param array|null $replaceSubDomain
     * @param array|null $currentSubDomain
     * @param string|null $logicSubdomain
     * @return string
     */
    public static function getReplaceUri($uri, $replaceSubDomain, $currentSubDomain = null, $logicSubdomain = null)
    {
        if(!$currentSubDomain) {
            $currentSubDomain = Base::GetCurrentDomain();
        }
        if(!$logicSubdomain) {
            $logicSubdomain = Config::getInstance()->get('logic_subdomain');
        }

        $arUrl = parse_url($uri);

        if(empty($arUrl['scheme'])) {
            if(Context::getCurrent()->getRequest()->isHttps()) {
                $arUrl['scheme'] = 'https://';
            } else {
                $arUrl['scheme'] = 'http://';
            }
        }

        $arUrl['path'] = self::getReplacePath($arUrl['path'], $replaceSubDomain, $currentSubDomain, $logicSubdomain);

        if ($logicSubdomain === SubDomain::LOGIC_DIRECTORY) {
            $arUrl['host'] = $replaceSubDomain['UF_DOMAIN'];
        } elseif ($logicSubdomain === SubDomain::LOGIC_SUBDOMAIN) {
            if ($replaceSubDomain['UF_SUBDOMAIN'] === 'main') {
                $arUrl['host'] = $replaceSubDomain['UF_DOMAIN'];
            } else {
                $arUrl['host'] = "{$replaceSubDomain['UF_SUBDOMAIN']}.{$replaceSubDomain['UF_DOMAIN']}";
            }
        }

        return self::buildUrl($arUrl);
    }

    /**
     * @param string $path
     * @param array|object $replaceSubDomain
     * @param array|null $currentSubDomain
     * @param string|null $logicSubdomain
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getReplacePath($path, $replaceSubDomain, $currentSubDomain = null, $logicSubdomain = null)
    {
        if(!$currentSubDomain) {
            $currentSubDomain = Base::GetCurrentDomain();
        }
        if(!$logicSubdomain) {
            $logicSubdomain = Config::getInstance()->get('logic_subdomain');
        }

        $path = parse_url($path, PHP_URL_PATH);

        if (is_object($replaceSubDomain)) {
            $replaceSubDomain = $replaceSubDomain->toArray();
        }

        if($currentSubDomain['UF_SUBDOMAIN'] !== $replaceSubDomain['UF_SUBDOMAIN']
            && $logicSubdomain === SubDomain::LOGIC_DIRECTORY
        ) {
            $replacePath = $replaceSubDomain['UF_SUBDOMAIN'];
            if ($replacePath === 'main') {
                $replacePath = '';
            }
            $routeRule = UrlRewriter::getRouteByUri($path);
            if (!empty($routeRule['CONDITION'])) {
                if (preg_match($routeRule['CONDITION'], $path, $matches)) {
                    if (!empty($matches['subdomain'])) {
                        $path = preg_replace(
                            "#^/{$matches['subdomain']}#",
                            $replacePath,
                            $path
                        );
                    } else {
//                        $path = "/{$replacePath}{$path}";
                        $path = static::getReplacePathForce($path, $currentSubDomain);
                    }
                }
            } elseif ($replacePath) {
//                $path = "/{$replacePath}{$path}";
                $path = static::getReplacePathForce($path, $currentSubDomain);
            }
        } elseif ($logicSubdomain === SubDomain::LOGIC_DIRECTORY) {
            $path = static::getReplacePathForce($path, $currentSubDomain);
        }
        return $path;
    }

    /**
     * @param string $path
     * @param array|null $currentSubDomain
     * @return string
     */
    public static function getReplacePathForce(string $path, ?array $currentSubDomain = null): string
    {
        if(!$currentSubDomain) {
            $currentSubDomain = Base::GetCurrentDomain();
        }
        if ($currentSubDomain['UF_SUBDOMAIN'] === SubDomain::DEFAULT_SUBDOMAIN) {
            return $path;
        }
        if (!preg_match("#^/{$currentSubDomain['UF_SUBDOMAIN']}#", $path)) {
            $path = "/{$currentSubDomain['UF_SUBDOMAIN']}{$path}";
        }

        return $path;
    }

    /**
     * @param string $content
     * @return array
     */
    protected static function findLinks($content)
    {
        if (!preg_match_all('/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ \'\"\s]*([^ \"\'>\s#]+)[^>]*>/ium', $content, $matches)) {
            return [];
        }
        $links = $matches[1] ?? [];

        if($links) {
            $links = array_unique($links);
            foreach ($links as $k => $link) {
                if(
                    !preg_match('#(^\/(?![\/])(?!(?:ru|en|de)\/))#', $link)
                    || preg_match('#^\/.*?\.\w+$#', $link)
                ) {
                    unset($links[$k]);
                }
            }
        }

        return $links;
    }

    /**
     * Return form actions
     * @param string $content
     * @return string[]
     */
    protected static function findFormActions(string $content)
    {
        if (!preg_match_all('/<[Ff][Oo][Rr][Mm][\s]{1}[^>]*[Aa][Cc][Tt][Ii][Oo][Nn][^=]*=[ \'\"\s]*([^ \"\'>\s#]+)[^>]*>/ium', $content, $matches)) {
            return [];
        }
        $links = $matches[1] ?? [];

        if($links) {
            $links = array_unique($links);
            foreach ($links as $k => $link) {
                if(
                    !preg_match('#(^\/(?![\/])(?!(?:ru|en|de)\/))#', $link)
                    || preg_match('#^\/.*?\.\w+$#', $link)
                ) {
                    unset($links[$k]);
                }
            }
        }

        return $links;
    }

    /**
     * @param array $arUrl
     * @param null|boolean $ssl
     * @return string
     * @since 1.1.0
     */
    public static function buildUrl($arUrl, $ssl = null)
    {
        $url = '';
        if(!empty($arUrl['host'])) {
            if(!empty($arUrl['scheme'])) {
                $url = $arUrl['scheme'];
            } else {
                if(!isset($ssl)) {
                    $ssl = Context::getCurrent()->getRequest()->isHttps();
                }
                if($ssl) {
                    $url = 'https://';
                } else {
                    $url = 'http://';
                }
            }
            if(!empty($arUrl['user']) && !empty($arUrl['pass'])) {
                $url .= "{$arUrl['user']}:{$arUrl['pass']}@";
            }
            $url .= $arUrl['host'];
        }
        if(!empty($arUrl['port']) && !\in_array($arUrl['port'],['80','8080','443'])) {
            $url .= ":{$arUrl['port']}";
        }
        if(!empty($arUrl['path'])) {
            $url .= $arUrl['path'];
        }
        if(!empty($arUrl['query'])) {
            $url .= "?{$arUrl['query']}";
        }
        if(!empty($arUrl['fragment'])) {
            $url .= "#{$arUrl['fragment']}";
        }
        return $url;
    }
}
