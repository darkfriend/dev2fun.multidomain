<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
 * @version 0.1.24
 */
$this->setFrameMode(true);
?>
<?if(!empty($arResult['ITEMS'])){?>
	<ul>
	<?foreach ($arParams['ITEMS'] as $item):?>
		<li><?=$item?></li>
	<?endforeach;?>
	</ul>
<?}?>