<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CIntranetToolbar $INTRANET_TOOLBAR */
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.1.0
 */
global $INTRANET_TOOLBAR;

use Bitrix\Main\Context,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Loader,
    Bitrix\Iblock;

if (!isset($arParams["CACHE_TIME"]))
    $arParams["CACHE_TIME"] = 36000000;

Loader::includeModule('dev2fun.multidomain');

$arParams["SORT_BY1"] = trim($arParams["SORT_BY1"]);
if (strlen($arParams["SORT_BY1"]) <= 0)
    $arParams["SORT_BY1"] = "UF_NAME";
if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER1"]))
    $arParams["SORT_ORDER1"] = "DESC";

// FILTER
$arrFilter = [];
if (
    strlen($arParams["FILTER_NAME"]) > 0
    && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])
    && isset($GLOBALS[$arParams["FILTER_NAME"]])
) {
    $arrFilter = $GLOBALS[$arParams["FILTER_NAME"]];
}
if (!is_array($arrFilter)) {
    $arrFilter = [];
}

//ORDER BY
$arSort = [
    $arParams["SORT_BY1"] => $arParams["SORT_ORDER1"],
];
if (!array_key_exists("ID", $arSort)) $arSort["ID"] = "DESC";
$hlID = Bitrix\Main\Config\Option::get('dev2fun.multidomain', 'highload_domains', '', SITE_ID);

if ($this->startResultCache(
    false,
    [
        ($arParams["CACHE_GROUPS"] === "N" ? false : $USER->GetGroups()),
        $arParams,
        $arrFilter,
        $hlID,
        \Dev2fun\MultiDomain\Base::GetCurrentDomain()
    ]
)) {
    $arResult = [
        'CURRENT' => \Dev2fun\MultiDomain\Base::GetCurrentDomain(),
        'SUBDOMAIN' => \Dev2fun\MultiDomain\SubDomain::getInstance(),
    ];
    $hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
    if ($hlID) {
        $arResult['ITEMS'] = $hl->getElementList($hlID, $arrFilter, $arSort);
    } else {
        $arResult['ITEMS'] = [];
    }
    $this->setResultCacheKeys([
        "ITEMS",
        "CURRENT",
    ]);
}

//$this->includeComponentTemplate();