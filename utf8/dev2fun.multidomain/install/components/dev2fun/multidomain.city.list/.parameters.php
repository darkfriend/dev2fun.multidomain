<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 0.1.29
 */
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Iblock;
use Bitrix\Currency;
use Bitrix\Main\Config\Option;

global $USER_FIELD_MANAGER;

if (!Loader::includeModule('iblock')) return;
if (!Loader::includeModule('dev2fun.multidomain')) return;
if (!Loader::includeModule('highloadblock')) return;

$hlIBlockId = Bitrix\Main\Config\Option::get('dev2fun.multidomain','highload_domains','',SITE_ID);
$arSortFields = [];
if($hlIBlockId) {
	$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
	$entityTable = $hl->getElementsResource($hlIBlockId); // ($hlIBlockId);
	$fields = $entityTable->getFields();
	foreach ($fields as $field) {
		$columnField = $field->getColumnName();
		$arSortFields[] = $columnField;
	}
}

$arSorts = array(
	"ASC" => GetMessage("MULTIDOMAIN.CITY.LIST_IBLOCK_DESC_ASC"),
	"DESC" => GetMessage("MULTIDOMAIN.CITY.LIST_IBLOCK_DESC_DESC")
);

$arComponentParameters = [
	"GROUPS" => [],
	"PARAMETERS" => [
		"SORT_BY1" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("MULTIDOMAIN.CITY.LIST_IBLOCK_DESC_IBORD1"),
			"TYPE" => "LIST",
			"DEFAULT" => "ID",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_ORDER1" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("MULTIDOMAIN.CITY.LIST_IBLOCK_DESC_IBBY1"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"FILTER_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("MULTIDOMAIN.CITY.LIST_IBLOCK_FILTER"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
	],
];