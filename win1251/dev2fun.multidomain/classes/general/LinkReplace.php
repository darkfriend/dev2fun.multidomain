<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;


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
        static::$links = static::findLinks($content);
        if(self::$links) {
            foreach (self::$links as $link) {
                $content = preg_replace(
                    "#href=\"($link)\"#ium",
                    "href=\"/{$currentSubDomain['UF_SUBDOMAIN']}$1\"",
                    $content
                );
            }
        }
        return $content;
    }

    /**
     * @param string $uri
     * @param array $replaceSubDomain
     * @param array $currentSubDomain
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

        if(\CMain::IsHTTPS()) {
            $result = 'https://';
        } else {
            $result = 'http://';
        }

        if($currentSubDomain['UF_SUBDOMAIN'] !== $replaceSubDomain['UF_SUBDOMAIN']
            && $logicSubdomain === SubDomain::LOGIC_DIRECTORY
        ) {
            $replacePath = $replaceSubDomain['UF_SUBDOMAIN'];
            if ($replacePath === 'main') {
                $replacePath = '';
            }
            //            else if($currentSubDomain['UF_SUBDOMAIN'] !== ) {
            //                $uri = "/{$replacePath}{$uri}";
            //            }
            $routeRule = UrlRewriter::getRouteByUri($uri);
            if (!empty($routeRule['CONDITION'])) {
                if (preg_match($routeRule['CONDITION'], $uri, $matches)) {
                    if (!empty($matches['subdomain'])) {
                        $uri = preg_replace(
                            "#^/{$matches['subdomain']}#",
                            $replacePath,
                            $uri
                        );
                    } else {
                        $uri = "/{$replacePath}{$uri}";
                    }
                }
            } elseif ($replacePath) {
                $uri = "/{$replacePath}{$uri}";
            }
        }

        if ($logicSubdomain === SubDomain::LOGIC_DIRECTORY) {
            $result .= $replaceSubDomain['UF_DOMAIN'];
        } elseif ($logicSubdomain === SubDomain::LOGIC_SUBDOMAIN) {
            if ($replaceSubDomain['UF_SUBDOMAIN'] === 'main') {
                $result .= $replaceSubDomain['UF_DOMAIN'];
            } else {
                $result .= "{$replaceSubDomain['UF_SUBDOMAIN']}.{$replaceSubDomain['UF_DOMAIN']}";
            }
        }

        return $result.$uri;
    }

    /**
     * @param string $content
     * @return array
     */
    protected static function findLinks($content)
    {
        if(!preg_match_all('#<a.*href="(.*?)"#ium', $content, $matches)) {
            return [];
        }
        $links = $matches[1] ?? [];
        if($links) {
            $links = array_unique($links);
            foreach ($links as $k => $link) {
                if(!preg_match('#(^/(?![/])(?!(?:ru|en)/))#', $link)) {
                    unset($links[$k]);
                }
            }
        }
        return $links;
    }
}
