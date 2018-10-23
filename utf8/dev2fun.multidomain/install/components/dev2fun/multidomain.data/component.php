<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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
 * @version 0.1.24
 */
global $INTRANET_TOOLBAR;

use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

Loader::includeModule('dev2fun.multidomain');
$arResult = \Dev2fun\MultiDomain\Base::GetCurrentDomain();
//if($this->startResultCache(
//	false,
//	array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $arParams["HL_ID"], $currentDomain)
//))
//{
//	$arResult = $currentDomain;
//	$this->setResultCacheKeys(array(
//		"ID",
//		"IBLOCK_TYPE_ID",
//		"LIST_PAGE_URL",
//		"NAV_CACHED_DATA",
//		"NAME",
//		"SECTION",
//		"ELEMENTS",
//		"IPROPERTY_VALUES",
//		"ITEMS_TIMESTAMP_X",
//	));
//}
$this->includeComponentTemplate();