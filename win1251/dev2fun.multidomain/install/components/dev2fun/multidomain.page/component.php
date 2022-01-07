<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
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

if (!isset($arParams["CACHE_TIME"])) {
    $arParams["CACHE_TIME"] = 36000000;
}

Loader::includeModule('dev2fun.multidomain');

if (empty($arParams["PAGE"])) {
    ShowError('Не указан путь до страницы');
    return;
}

if (empty($arParams["LANG"])) {
    $arParams["LANG"] = \Bitrix\Main\Application::getInstance()->getContext()->getLanguage();
}

$arResult = [
    'TEMPLATE' => $componentTemplate,
    'PAGE' => $arParams['PAGE'],
    'LANG' => $arParams['LANG'],
];

$this->includeComponentTemplate();