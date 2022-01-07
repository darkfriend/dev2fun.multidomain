<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arCurrentValues */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.1.0
 */

use Bitrix\Main\Loader;

global $USER_FIELD_MANAGER;

if (!Loader::includeModule('iblock')) return;
if (!Loader::includeModule('dev2fun.multidomain')) return;
if (!Loader::includeModule('highloadblock')) return;

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "PAGE" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("MULTIDOMAIN_COMPONENT_PAGE.PATH_DESC"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "LANG" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("MULTIDOMAIN_COMPONENT_PAGE.LANG_DESC"),
            "TYPE" => "STRING",
        ],
    ],
];