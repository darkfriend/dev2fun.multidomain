<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.1.4
 */

namespace Dev2fun\MultiDomain;

\defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

include_once __DIR__ . '/classes/composer/vendor/autoload.php';

IncludeModuleLangFile(__FILE__);

global $DBType;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Localization\Loc;

Loader::registerAutoLoadClasses(
    "dev2fun.multidomain",
    [
        'Dev2fun\MultiDomain\Base' => __FILE__,
        'Dev2fun\MultiDomain\SubDomain' => 'classes/general/SubDomain.php',
        'Dev2fun\MultiDomain\Seo' => 'classes/general/Seo.php',
        'Dev2fun\MultiDomain\Geo' => 'classes/general/Geo.php',
        'Dev2fun\MultiDomain\HLHelpers' => 'lib/HLHelpers.php',
        'Dev2fun\MultiDomain\Config' => 'classes/general/Config.php',
        'Dev2fun\MultiDomain\TemplateSeo' => 'classes/general/TemplateSeo.php',
        'Dev2fun\MultiDomain\TabOptions' => 'classes/general/TabOptions.php',
        'Dev2fun\MultiDomain\LangData' => 'classes/general/LangData.php',
        'Dev2fun\MultiDomain\LinkReplace' => 'classes/general/LinkReplace.php',
        'Dev2fun\MultiDomain\UrlRewriter' => 'classes/general/UrlRewriter.php',
        'Dev2fun\MultiDomain\SeoReplace' => 'classes/general/SeoReplace.php',
        'Dev2fun\MultiDomain\Site' => 'classes/general/Site.php',

        'Dev2fun\MultiDomain\TemplateSeoTitleCalculate' => 'classes/general/TemplateSeoTitleCalculate.php',
        'Dev2fun\MultiDomain\TemplateSeoDescriptionCalculate' => 'classes/general/TemplateSeoDescriptionCalculate.php',
        'Dev2fun\MultiDomain\TemplateSeoKeywordsCalculate' => 'classes/general/TemplateSeoKeywordsCalculate.php',
        'Dev2fun\MultiDomain\TemplateSeoHeadingCalculate' => 'classes/general/TemplateSeoHeadingCalculate.php',
        'Dev2fun\MultiDomain\TemplateLangFieldCalculate' => 'classes/general/TemplateLangFieldCalculate.php',
    ]
);

class Base
{
    private static $currentDomain = [];
    private static $currentSeo = [];
    private static $isInit = false;

    public static $module_id = 'dev2fun.multidomain';

    public static function InitDomains()
    {
        $config = Config::getInstance();

        if($config->get('enable', 'N') !== 'Y') {
            self::$isInit = true;
            return true;
        }

        if(static::isExcludedPath()) {
            return true;
        }

        if (!Loader::includeModule('highloadblock')) {
            throw new \Exception(Loc::getMessage("NO_INSTALL_HIGHLOADBLOCK"));
        }
        if (!Loader::includeModule('iblock')) {
            throw new \Exception(Loc::getMessage("NO_INSTALL_IBLOCK"));
        }

        $oSubDomain = SubDomain::getInstance();
        if (!$oSubDomain->check()) {
            self::$isInit = true;
            return null;
        }

        $logicSubdomain = $config->get('logic_subdomain');
        $activeAutoRewrite = $config->get('auto_rewrite', 'N') === 'Y';
        if($logicSubdomain === SubDomain::LOGIC_DIRECTORY && $activeAutoRewrite) {
            $subDomainProps = $oSubDomain->getCurrent();
            $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if($subDomainProps['UF_SUBDOMAIN'] !== 'main') {
                $urlRewrites = \Bitrix\Main\UrlRewriter::getList(SITE_ID, [
                    'QUERY' => "/{$subDomainProps['UF_SUBDOMAIN']}{$requestUri}",
                ]);
                if(!$urlRewrites) {
                    if(!UrlRewriter::isPath($requestUri)) {
                        $requestRewrite = \Bitrix\Main\UrlRewriter::getList(SITE_ID, [
                            'QUERY' => $requestUri,
                        ]);
                        $rewriteUri = $requestUri;
                        if($requestRewrite) {
                            $requestRewrite = current($requestRewrite);
                            $path = str_replace('index.php', '', $requestRewrite['PATH']);
                            $rewriteUri = $path;
                        }
                    } else {
                        $rewriteUri = $requestUri;
                    }
                    if(!UrlRewriter::add($rewriteUri, SITE_ID)) {
                       return false;
                    }
                    if ($requestUri === '/') {
                        $redirectUrl = "/{$subDomainProps['UF_SUBDOMAIN']}/";
                    } else {
                        $redirectUrl = preg_replace(
                            '#/(.*)/#',
                            '/' . ltrim("{$subDomainProps['UF_SUBDOMAIN']}/$1/", '/'),
                            $_SERVER['REQUEST_URI']
                        );
                    }
                    LocalRedirect($redirectUrl);
                }
            }
        }

        self::$currentDomain = $oSubDomain->getCurrent();
        self::$isInit = true;
        if (self::$currentDomain) {
            $asset = Asset::getInstance();
            $asset->addString('<meta name="dev2fun" content="module:dev2fun.multidomain">');
            $asset->addString('<!-- dev2fun.multidomain -->');
//			if(!empty($subDomainProps['UF_CODE_COUNTERS'])) {
//				$asset->addString($subDomainProps['UF_CODE_COUNTERS']);
//			}
            if (!empty(self::$currentDomain['UF_META_TAGS'])) {
                $asset->addString(self::$currentDomain['UF_META_TAGS']);
            }

            if($config->get('enable_hreflang') === 'Y') {
                $defaultLang = $config->get('lang_default');
                if(!$defaultLang) {
                    $defaultLang = $oSubDomain->getDefaultLang();
                }
                $defaultLangUri = LinkReplace::getReplaceUri(
                    $GLOBALS['APPLICATION']->GetCurUri(),
                    $oSubDomain->getDomainByFilter(function($item) use ($defaultLang) {
                        return $defaultLang === $item['UF_LANG'];
                    }),
                    self::$currentDomain,
                    $logicSubdomain
                );
                $hrefLangs = [
                    '<link rel="alternate" hreflang="x-default"
       href="'.$defaultLangUri.'" />',
                ];
                foreach ($oSubDomain->getDomainList() as $item) {
                    $hrefLang = '<link rel="alternate" hreflang="{lang}" href="{href}" />';
                    $hrefLangs[] = strtr($hrefLang, [
                        '{lang}' => $item['UF_LANG'],
                        '{href}' => LinkReplace::getReplaceUri(
                            $GLOBALS['APPLICATION']->GetCurUri(),
                            $item,
                            self::$currentDomain,
                            $logicSubdomain
                        ),
                    ]);
                }
                foreach ($hrefLangs as $hrefLang) {
                    $asset->addString($hrefLang);
                }
            }

            $asset->addString('<!-- /dev2fun.multidomain -->');
        }
        return true;
    }

    /**
     * Возвращает массив с данными о текущем домене
     * @return array ID|UF_NAME|UF_SUBDOMAIN|UF_DOMAIN|UF_CODE_COUNTERS|UF_META_TAGS|UF_LANG
     */
    public static function GetCurrentDomain()
    {
        if(!self::$isInit) {
            self::InitDomains();
        }
        return self::$currentDomain;
    }

    /**
     * @return string
     */
    public static function getSefFolder()
    {
        $current = static::GetCurrentDomain();
        if(empty($current['UF_SUBDOMAIN'])) {
            return '';
        }
        if($current['UF_SUBDOMAIN'] === 'main') {
            return '';
        }
        return "/{$current['UF_SUBDOMAIN']}";
    }

    /**
     * @return bool
     * @throws LoaderException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function InitSeoDomains()
    {
        global $APPLICATION, $USER;

        $config = Config::getInstance();
        if($config->get('enable', 'N') !== 'Y') {
            return true;
        }

        if(static::isExcludedPath()) {
            return true;
        }

        if (!Loader::includeModule('highloadblock')) {
            throw new LoaderException(Loc::getMessage("NO_INSTALL_HIGHLOADBLOCK"));
        }
        if (!Loader::includeModule('iblock')) {
            throw new LoaderException(Loc::getMessage("NO_INSTALL_IBLOCK"));
        }

        $enable = $config->get('enable_seo_page');
        if ($enable !== 'Y') {
            return true;
        }

        $moduleId = self::$module_id;

        if ($USER->IsAdmin()) {
            \CJSCore::Init(['ajax', 'window', 'jquery']);
            $asset = Asset::getInstance();
            $asset->addString('<meta name="dev2fun" content="module:dev2fun.multidomain:SEO">');
            //		$asset->addString('<script src="http://www.sphereshot.co/wp-content/themes/sphereshot/js/vendor/modernizr-2.8.3.min.js"></script>');
            $asset->addString('<script type="text/javascript" src="/bitrix/js/' . $moduleId . '/jquery.magnific-popup.min.js" defer></script>', false, AssetLocation::AFTER_JS_KERNEL);
            $asset->addString('<script type="text/javascript" src="/bitrix/js/' . $moduleId . '/seo.js" defer></script>', true, AssetLocation::AFTER_JS_KERNEL);

//		$asset->addCss('/bitrix/css/'.$moduleId.'/magnific-popup.css');
//		$asset->addJs('/bitrix/js/'.$moduleId.'/jquery.magnific-popup.min.js');

            $asset->addString('<link rel="stylesheet" type="text/css" href="/bitrix/css/' . $moduleId . '/seo.css">');
            $asset->addString('<link rel="stylesheet" type="text/css" href="/bitrix/css/' . $moduleId . '/magnific-popup.css">');
        }

//        $asset->addCss('/bitrix/css/'.$moduleId.'/seo.css');
//        $asset->addJs('/bitrix/js/'.$moduleId.'/seo.js');

        $seoHlId = Option::get($moduleId, 'highload_domains_seo');
        $seo = Seo::getInstance();
        self::$currentSeo = $seo->show($seoHlId);

        return true;
    }

    /**
     * @param string $content
     * @return string
     */
    public static function InitBufferContent(&$content)
    {
        global $APPLICATION;

        $config = Config::getInstance();
        if($config->get('enable', 'N') !== 'Y') {
            return $content;
        }

        if(empty(self::$currentDomain['UF_SUBDOMAIN'])) {
            return $content;
        }

        if(static::isExcludedPath()) {
            return $content;
        }

        if (!empty(self::$currentDomain['UF_CODE_COUNTERS'])) {
            $content = \preg_replace(
                '#(\<\/body\>)#',
                self::$currentDomain['UF_CODE_COUNTERS'] . '</body>',
                $content
            );
        }

        if($config->get('enable_replace_links') === 'Y' && $config->get('logic_subdomain') === SubDomain::LOGIC_DIRECTORY) {
            $content = LinkReplace::process($content);
        }

        SeoReplace::process($content);

        $enable = $config->get('enable_seo_page');
        if ($enable !== 'Y') {
            return $content;
        }

        if (\preg_match('#\#(DEV2FUN_SEO_TEXT|SEO_TEXT|TEXT)\##', $content, $matches)) {
            $replaceText = '';
            if (!empty(self::$currentSeo['UF_TEXT'])) {
                $replaceText = self::$currentSeo['UF_TEXT'];
            }
            $content = strtr($content, [
                '#' . $matches[1] . '#' => $replaceText,
            ]);
        }
        if (!empty(self::$currentSeo['UF_H1'])) {
            $APPLICATION->SetPageProperty('h1', self::$currentSeo['UF_H1']);
        }
        if (\preg_match('#\#(H1_TEXT|H1)\##', $content, $matches)) {
            $replaceText = '';
            if (!empty(self::$currentSeo['UF_H1'])) {
                $replaceText = self::$currentSeo['UF_H1'];
            }
            $content = \strtr($content, [
                '#' . $matches[1] . '#' => $replaceText,
            ]);
        }

        return $content;
    }

    /**
     * Check current path for excluded
     * @return bool
     */
    public static function isExcludedPath()
    {
        global $APPLICATION;
        $currentPage = $APPLICATION->GetCurUri();
        $arExcludePath = Config::getInstance()->get('exclude_path', []);
        if(!is_array($arExcludePath)) {
            $arExcludePath = (array) $arExcludePath;
        }
        array_unshift(
            $arExcludePath,
            '\/(bitrix|local)\/'
        );
        foreach ($arExcludePath as $excludePath) {
            if (\preg_match('#' . $excludePath . '#i', $currentPage)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     * @since 1.1.0
     */
    public static function getLanguage()
    {
        return \Bitrix\Main\Application::getInstance()->getContext()->getLanguage();
    }

    /**
     * @return string
     */
    public static function getTypePage()
    {
        global $APPLICATION;
        $curPath = $APPLICATION->GetCurPage();
        $mode = 'element';
        switch ($curPath) {
            case (\preg_match('#(iblock_element_edit)#', $curPath) == 1) :
            case (\preg_match('#(highloadblock_row_edit)#', $curPath) == 1) :
                $mode = 'element';
                break;
            case (\preg_match('#(iblock_section_edit)#', $curPath) == 1) :
                $mode = 'section';
                break;
        }
        return $mode;
    }

    /**
     * @return bool
     */
    public static function IsAddTab()
    {
        global $APPLICATION;

        $config = Config::getInstance();
        if($config->get('enable', 'N') !== 'Y') {
            return false;
        }

        return (bool) preg_match(
            '#(iblock_element_edit|highloadblock_row_edit|iblock_section_edit)#',
            $APPLICATION->GetCurPage()
        );
    }

    /**
     * @param \CAdminTabControl $form
     */
    public static function AddAdminLangTab(&$form)
    {
        \Bitrix\Main\Loader::includeModule('dev2fun.multidomain');
        \Bitrix\Main\Loader::includeModule("iblock");
        if (self::IsAddTab()) {
            ob_start();
            include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/dev2fun.multidomain/include/admin/admin.php';

            $admList = ob_get_contents();
            ob_end_clean();

            $form->tabs[] = [
                "DIV" => "dev2fun_multilang_tab_list",
                "TAB" => Loc::getMessage('D2F_MULTIDOMAIN_TAB_NAME'),
                "ICON" => "main_user_edit",
                "TITLE" => Loc::getMessage('D2F_MULTIDOMAIN_TAB_TITLE'),
                "CONTENT" => '<tr><td colspan="2">' . $admList . '</td></tr>',
            ];
        }
    }

    public static function OnBeforeIBlockElementUpdate(&$arFields)
    {
        \Dev2fun\MultiDomain\LangData::saveElement($arFields);
    }

    public static function OnAfterIBlockElementDelete($arFields)
    {
        \Dev2fun\MultiDomain\LangData::deleteElement($arFields);
    }

    public static function OnAfterIBlockSectionEvent(&$arFields)
    {
        \Dev2fun\MultiDomain\LangData::saveElement($arFields);
    }

    public static function OnAfterIBlockSectionDelete($arFields)
    {
        $arFields['REF_TYPE'] = 'section';
        \Dev2fun\MultiDomain\LangData::deleteElement($arFields);
    }

    public static function ShowThanksNotice()
    {
        \CAdminNotify::Add([
            'MESSAGE' => \Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_DONATE_MESSAGES', ['#URL#' => '/bitrix/admin/settings.php?lang=ru&mid=dev2fun.multidomain&mid_menu=1&tabControl_active_tab=editDonate']),
            'TAG' => 'dev2fun_multidomain_update',
            'MODULE_ID' => 'dev2fun.multidomain',
        ]);
    }
}