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
 * @version 1.2.7
 */
$this->setFrameMode(true);

$baseDir = \realpath($_SERVER['DOCUMENT_ROOT'] . $templateFolder);
$lang = \preg_replace('#[^A-Za-z0-9_\-]#', '', (string) $arResult['LANG']);
$page = \preg_replace('#[^A-Za-z0-9_\-/]#', '', (string) $arResult['PAGE']);
$path = $baseDir ? \realpath($baseDir . '/' . $lang . '/' . $page . '.php') : false;

if (!$path || \strpos($path, $baseDir . \DIRECTORY_SEPARATOR) !== 0 || !\is_file($path)) {
    ShowError("Файл \"{$arResult['PAGE']}.php\" не найден для \"{$arResult['LANG']}\"");
    return;
}

include $path;