<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 0.2.1
 */

namespace Dev2fun\MultiDomain;

\defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

include_once __DIR__ . '/classes/composer/vendor/autoload.php';

IncludeModuleLangFile(__FILE__);

global $DBType;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
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
    ]
);

class Base
{
    private static $currentDomain = [];
    private static $currentSeo = [];

    public static $module_id = 'dev2fun.multidomain';

    public static function InitDomains()
    {
        global $APPLICATION;
        $currentPage = $APPLICATION->GetCurUri();

        $config = Config::getInstance();
        $arExcludePath = $config->get('exclude_path');

        if ($arExcludePath) {
            foreach ($arExcludePath as $excludePath) {
                if (\preg_match('#' . $excludePath . '#i', $currentPage)) return true;
            }
        }

        if (!Loader::includeModule('highloadblock')) {
            throw new \Exception(Loc::getMessage("NO_INSTALL_HIGHLOADBLOCK"));
        }
        if (!Loader::includeModule('iblock')) {
            throw new \Exception(Loc::getMessage("NO_INSTALL_IBLOCK"));
        }

        $oSubDomain = new SubDomain();

        if (!$domain = $oSubDomain->check()) {
            return true;
        }
        $subDomainProps = $oSubDomain->getCurrent();

        self::$currentDomain = $subDomainProps;
        if ($subDomainProps) {
            $asset = Asset::getInstance();
            $asset->addString('<meta name="dev2fun" content="module:dev2fun.multidomain">');
            $asset->addString('<!-- dev2fun.multidomain -->');
            //			if(!empty($subDomainProps['UF_CODE_COUNTERS'])) {
            //				$asset->addString($subDomainProps['UF_CODE_COUNTERS']);
            //			}
            if (!empty($subDomainProps['UF_META_TAGS'])) {
                $asset->addString($subDomainProps['UF_META_TAGS']);
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
        return self::$currentDomain;
    }

    public static function InitSeoDomains()
    {
        global $APPLICATION, $USER;

        $currentPage = $APPLICATION->GetCurUri();

        if (\preg_match('#\/(bitrix|local)\/#', $currentPage))
            return true;

        if (!Loader::includeModule('highloadblock')) {
            throw new LoaderException(Loc::getMessage("NO_INSTALL_HIGHLOADBLOCK"));
        }
        if (!Loader::includeModule('iblock')) {
            throw new LoaderException(Loc::getMessage("NO_INSTALL_IBLOCK"));
        }

        $config = Config::getInstance();
        $enable = $config->get('enable_seo_page');
        if ($enable != 'Y') return true;

        $moduleId = self::$module_id;

        // изменение заголовка
        $enableAddCity = $config->get('enable_seo_title_add_city');
        if ($enableAddCity == 'Y' && !empty(self::$currentDomain['UF_NAME'])) {
            $title = $APPLICATION->GetPageProperty("title");
            $patternAddCity = $config->get('pattern_seo_title_add_city');
            if (!$patternAddCity) $patternAddCity = '#TITLE# - #CITY#';
            $title = \strtr($patternAddCity, [
                '#TITLE#' => $title,
                '#CITY#' => self::$currentDomain['UF_NAME'],
            ]);
            if ($title) {
                $APPLICATION->SetPageProperty("title", $title);
            }
        }

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

        //		$asset->addCss('/bitrix/css/'.$moduleId.'/seo.css');
        //		$asset->addJs('/bitrix/js/'.$moduleId.'/seo.js');

        $seoHlId = Option::get($moduleId, 'highload_domains_seo');
        $seo = Seo::getInstance();
        self::$currentSeo = $seo->show($seoHlId);

        return true;
    }

    public static function InitBufferContent(&$content)
    {
        global $APPLICATION, $USER;
        //		if(!$USER->IsAdmin()) return $content;
        $currentPage = $APPLICATION->GetCurUri();

        if (\preg_match('#\/(bitrix|local)\/#', $currentPage))
            return $content;

        if (!empty(self::$currentDomain['UF_CODE_COUNTERS'])) {
            $content = \preg_replace(
                '#(\<\/body\>)#',
                self::$currentDomain['UF_CODE_COUNTERS'] . '</body>',
                $content
            );
        }

        $config = Config::getInstance();
        $enable = $config->get('enable_seo_page');
        if ($enable != 'Y') return $content;

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
    }

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

    public static function IsAddTab()
    {
        global $APPLICATION;
        $curPath = $APPLICATION->GetCurPage();
        switch ($curPath) {
            case (\preg_match('#(iblock_element_edit)#', $curPath) == 1) :
            case (\preg_match('#(highloadblock_row_edit)#', $curPath) == 1) :
                //                $enableTabElement = Option::get(\dev2funModuleOpenGraphClass::$module_id, 'ADDTAB_ELEMENT', 'N');
                //                self::$_type = 'element';
                //                return ($enableTabElement == 'Y');
                return true;
                break;
            case (\preg_match('#(iblock_section_edit)#', $curPath) == 1) :
                return true;
                //                $enableTabSection = Option::get(\dev2funModuleOpenGraphClass::$module_id, 'ADDTAB_SECTION', 'N');
                //                self::$_type = 'section';
                //                return ($enableTabSection == 'Y');
                break;
        }
        return false;
    }

    /**
     * @param \CAdminTabControl $form
     */
    public static function AddAdminLangTab(&$form)
    {
        \Bitrix\Main\Loader::includeModule('dev2fun.multidomain');
        \Bitrix\Main\Loader::includeModule("iblock");
        if (self::IsAddTab()) {
            \ob_start();
            include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/dev2fun.multidomain/include/admin/admin.php';

            $admList = \ob_get_contents();
            \ob_end_clean();

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
            'MESSAGE' => \Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_DONATE_MESSAGES', ['#URL#' => '/bitrix/admin/settings.php?lang=ru&mid=dev2fun.multidomain&mid_menu=1&tabControl_active_tab=donate']),
            'TAG' => 'dev2fun_multidomain_update',
            'MODULE_ID' => 'dev2fun.multidomain',
        ]);
    }
}