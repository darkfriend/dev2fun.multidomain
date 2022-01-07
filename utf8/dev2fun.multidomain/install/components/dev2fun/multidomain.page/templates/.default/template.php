<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.1.0
 */
$this->setFrameMode(true);

$path = "{$_SERVER['DOCUMENT_ROOT']}{$templateFolder}/{$arResult['LANG']}/{$arResult['PAGE']}.php";

if (!\is_file($path)) {
    ShowError("Файл \"{$arResult['PAGE']}.php\" не найден для \"{$arResult['LANG']}\"");
    return;
}

include $path;