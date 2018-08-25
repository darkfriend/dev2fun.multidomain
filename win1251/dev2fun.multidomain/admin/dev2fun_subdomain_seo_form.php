<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright darkfriend
 * @version 0.1.19
 */
//error_reporting(E_PARSE|E_COMPILE_ERROR|E_ALL|E_WARNING);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
global $APPLICATION;
\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('highloadblock');
\Bitrix\Main\Loader::includeModule('dev2fun.multidomain');
$APPLICATION->RestartBuffer();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
$app = \Bitrix\Main\Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$seoTitle = $request->getPost('m_seo_title');
$seoDescription = $request->getPost('m_seo_description');
$seoKeywords = $request->getPost('m_seo_keywords');
$seoText = $request->getPost('m_seo_text');
$seoH1 = $request->getPost('m_seo_h1');

$seoHost = $request->getPost('m_seo_host');
$seoPage = $request->getPost('m_seo_page');

$seo = \Dev2fun\MultiDomain\Seo::getInstance();
$hlId = \Dev2fun\MultiDomain\Config::getInstance()->get('highload_domains_seo');
$arSeo = $seo->getDomain($hlId,$seoHost,$seoPage);

if($_SERVER['REQUEST_METHOD']=='POST' && check_bitrix_sessid()) {
	$arFields = [
		'UF_TITLE' => $seoTitle,
		'UF_DESCRIPTION' => $seoDescription,
		'UF_KEYWORDS' => $seoKeywords,
		'UF_DOMAIN' => $seoHost,
		'UF_PATH' => $seoPage,
		'UF_TEXT' => $seoText,
		'UF_H1' => $seoH1,
	];
	if($arSeo){
		$id = $arSeo['ID'];
		$arFields['ID'] = $id;
	}
	$res = $seo->setDomain($hlId,$arFields);
	if($res){
		$msg = \Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_UPDATE');
	} else {
		$msg = \Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_UPDATE_ERROR');
	}
	$content = '
        <div class="adm-workarea m_seo_edit_wrap">
            <header>
                <div class="adm-detail-title">'.\Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_SETTING_SEO').'</div>
            </header>
            <div class="m_seo_edit_form">
                <div class="m_seo_edit_form__message">
                    '.$msg.'
                </div>
            </div>
        </div>
    ';
	echo \Bitrix\Main\Web\Json::encode([
		'success' => true,
		'content' => $content,
	]);
	die();
}
if($arSeo) {
	if(!empty($arSeo['UF_TITLE'])) {
		$seoTitle = $arSeo['UF_TITLE'];
	}
	if(!empty($arSeo['UF_DESCRIPTION'])) {
		$seoDescription = $arSeo['UF_DESCRIPTION'];
	}
	if(!empty($arSeo['UF_KEYWORDS'])) {
		$seoKeywords = $arSeo['UF_KEYWORDS'];
	}
	if(!empty($arSeo['UF_TEXT'])) {
		$seoText = $arSeo['UF_TEXT'];
	}
	if(!empty($arSeo['UF_H1'])) {
		$seoH1 = $arSeo['UF_H1'];
	}
}
?>
<div class="adm-workarea m_seo_edit_wrap">
	<header>
		<div class="adm-detail-title"><?=\Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_SETTING_SEO')?></div>
	</header>
	<form action="/bitrix/admin/dev2fun_subdomain_seo_form.php" method="post" enctype="multipart/form-data" class="m_seo_edit_form" onsubmit="onSaveEditSeoD2FForm(this);return false;">
		<?= bitrix_sessid_post()?>
		<table class="adm-detail-content-table edit-table" id="m_seo_edit_table">
			<tbody>
            <tr>
                <td width="20%" class="adm-detail-content-cell-l">
                    <label for="m_seo_h1">
                        H1:
                    </label>
                </td>
                <td width="80%" class="adm-detail-content-cell-r">
                    <input type="text" name="m_seo_h1" value="<?=$seoH1?>">
                </td>
            </tr>
			<tr>
				<td width="20%" class="adm-detail-content-cell-l">
					<label for="m_seo_title">
						Title:
					</label>
				</td>
				<td width="80%" class="adm-detail-content-cell-r">
					<input type="text" name="m_seo_title" value="<?=$seoTitle?>">
				</td>
			</tr>

			<tr>
				<td width="20%" class="adm-detail-content-cell-l">
					<label for="m_seo_description">
						Description:
					</label>
				</td>
				<td width="80%" class="adm-detail-content-cell-r">
                    <textarea name="m_seo_description" cols="30" rows="10"><?=$seoDescription?></textarea>
				</td>
			</tr>

			<tr>
				<td width="20%" class="adm-detail-content-cell-l">
					<label for="m_seo_keywords">
						Keywords:
					</label>
				</td>
				<td width="80%" class="adm-detail-content-cell-r">
					<input type="text" name="m_seo_keywords" value="<?=$seoKeywords?>">
				</td>
			</tr>

            <tr>
                <td width="20%" class="adm-detail-content-cell-l">
                    <label for="m_seo_text">
                        Text:
                    </label>
                </td>
                <td width="80%" class="adm-detail-content-cell-r">
                    <textarea name="m_seo_text" id="" cols="30" rows="10"><?=$seoText?></textarea>
                </td>
            </tr>

			<tr>
				<td width="20%" class="adm-detail-content-cell-l">
					<label for="m_seo_host">
						Host:
					</label>
				</td>
				<td width="80%" class="adm-detail-content-cell-r">
					<input type="text" name="m_seo_host" value="<?=$seoHost?>">
				</td>
			</tr>
			<tr>
				<td width="20%" class="adm-detail-content-cell-l">
					<label for="m_seo_page">
						Page:
					</label>
				</td>
				<td width="80%" class="adm-detail-content-cell-r">
					<input type="text" name="m_seo_page" value="<?=$seoPage?>">
				</td>
			</tr>
			<tr>
				<td width="100%" colspan="2">
					<input type="submit" name="save" value="<?=\Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_SEO_SUBMIT_VALUE')?>" class="adm-btn-save seo_m_save">
				</td>
			</tr>
			</tbody>
		</table>
	</form>
</div>
