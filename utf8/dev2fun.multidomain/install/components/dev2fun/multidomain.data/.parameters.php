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

$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
$arBlocks = $hl->getList();
$arHLIBlock = [];
if($arBlocks) {
	foreach ($arBlocks as $item) {
		$arHLIBlock[$item['ID']] = $item['NAME'];
	}
}

$hlIBlockId = Bitrix\Main\Config\Option::get('dev2fun.multidomain','highload_domains','',SITE_ID);
$arIBlockFields = [];
if($hlIBlockId) {
	$excludeIblockFields = [
		'ID', 'UF_ACTIVE', 'UF_SUBDOMAIN',
		'UF_DOMAIN', 'UF_CODE_COUNTERS',
	];
	$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
	$entityTable = $hl->getElementsResource($hlIBlockId); // ($hlIBlockId);
	$fields = $entityTable->getFields();
	foreach ($fields as $field) {
		$columnField = $field->getColumnName();
		if(!in_array($columnField,$excludeIblockFields)) {
			$arIBlockFields[] = $columnField;
		}
	}
}

$arAscDesc = [
	"asc" => GetMessage("IBLOCK_SORT_ASC"),
	"desc" => GetMessage("IBLOCK_SORT_DESC"),
];

$arComponentParameters = [
	"GROUPS" => [],
	"PARAMETERS" => [
		"ADDITIONAL_FIELDS" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ADDITIONAL_FIELDS"), // 'Выводимые поля',
			"TYPE" => "LIST",
			"VALUES" => $arIBlockFields,
		],
	],
];