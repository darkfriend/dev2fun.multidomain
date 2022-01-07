<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
?>
<div class="search-item__btn">
    <?=file_get_contents(__DIR__.'/img/globe.svg')?>
    <?=$arResult['CURRENT']['UF_NAME']?>
    <?=file_get_contents(__DIR__.'/img/arrow.svg')?>
</div>
<ul class="search-block">
    <?php foreach ($arResult['ITEMS'] as $item) {
        if ($item['ID']===$arResult['CURRENT']['ID']) {
            continue;
        }
        $url = $arResult['SUBDOMAIN']->getRoute(
            $_SERVER['REQUEST_URI'],
            $item
        );
        ?>
        <li>
            <a href="<?=$url?>" class="language">
                <?=$item['UF_NAME']?>
            </a>
        </li>
    <?php } ?>
</ul>
