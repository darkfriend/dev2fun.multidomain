<?
/**
* @author dev2fun (darkfriend)
* @copyright darkfriend
* @version 0.1.17
*/

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}
$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();
$curModuleName = "dev2fun.multidomain";
//Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$aTabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "ICON" => "main_settings",
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET")
    ),
	array(
		"DIV" => "edit2",
		"TAB" => Loc::getMessage("D2F_MULTIDOMAIN_TAB_2"),
		"ICON" => "main_settings",
		"TITLE" => Loc::getMessage("D2F_MULTIDOMAIN_TAB_2_TITLE_SET")
	),
    array(
        "DIV" => "edit3",
        "TAB" => Loc::getMessage("D2F_MULTIDOMAIN_TAB_3"),
        "ICON" => "main_settings",
        "TITLE" => Loc::getMessage("D2F_MULTIDOMAIN_TAB_3_TITLE_SET")
    ),
    array(
        "DIV" => "edit4",
        "TAB" => Loc::getMessage("D2F_MULTIDOMAIN_TAB_4"),
        "ICON" => "main_settings",
        "TITLE" => Loc::getMessage("D2F_MULTIDOMAIN_TAB_4_TITLE_SET")
    ),
	array(
		"DIV" => "donate",
		"TAB" => Loc::getMessage('SEC_DONATE_TAB'),
		"ICON"=>"main_user_edit",
		"TITLE"=>Loc::getMessage('SEC_DONATE_TAB_TITLE'),
	),
//	array(
//		"DIV" => "edit5",
//		"TAB" => Loc::getMessage("D2F_MULTIDOMAIN_TAB_5"),
//		"ICON" => "main_settings",
//		"TITLE" => Loc::getMessage("D2F_MULTIDOMAIN_TAB_5_TITLE_SET")
//	),
//    array("DIV" => "edit8", "TAB" => GetMessage("MAIN_TAB_8"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_OPTION_EVENT_LOG")),
//    array("DIV" => "edit5", "TAB" => GetMessage("MAIN_TAB_5"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_OPTION_UPD")),
//    array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);

//$tabControl = new CAdminTabControl("tabControl", array(
//    array(
//        "DIV" => "edit1",
//        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
//        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
//    ),
//));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($request->isPost() && check_bitrix_sessid()) {

    $arFields = [];
	$arFields['logic_subdomain']=$request->getPost('logic_subdomain');
	$arFields['type_subdomain']=$request->getPost('type_subdomain');
	$arFields['key_ip']=$request->getPost('key_ip');
	$arFields['domain_default']=$request->getPost('domain_default');

	// seo tab
	$arFields['enable_seo_page']=$request->getPost('enable_seo_page');
	$arFields['enable_seo_title_add_city']=$request->getPost('enable_seo_title_add_city');
	$arFields['pattern_seo_title_add_city']=$request->getPost('pattern_seo_title_add_city');

	$maplist=$request->getPost('MAPLIST');
	if($maplist) {
		foreach ($maplist as $k=>$v) {
			if(!$v['KEY']||!$v['SUBNAME']) {
				unset($maplist[$k]);
			}
		}
		if($maplist) {
		    $maplist = serialize($maplist);
		} else {
			$maplist = '';
		}
		$arFields['mapping_list']=$maplist;
    }
	$exlist=$request->getPost('EXCLUDE_PATH');
	if($exlist) {
		foreach ($exlist as $k=>$v) {
			if(!$v) {
				unset($exlist[$k]);
			}
		}
		if($exlist){
			$exlist = serialize($exlist);
        } else {
			$exlist = '';
        }
		$arFields['exclude_path']=$exlist;
	}
	$arFields['enable_multilang']=$request->getPost('enable_multilang');
	$arFields['lang_default']=$request->getPost('lang_default');
	$arFields['lang_default']=$request->getPost('lang_default');

	foreach ($arFields as $k=>$arField) {
		Option::set($curModuleName,$k,$arField);
	}
}
$msg = new CAdminMessage([
	'MESSAGE' => Loc::getMessage("D2F_MULTIDOMAIN_DONATE_MESSAGES",['#URL#'=>'/bitrix/admin/settings.php?lang=ru&mid=dev2fun.multidomain&mid_menu=1&tabControl_active_tab=donate']),
	'TYPE' => 'OK',
	'HTML' => true,
]);
echo $msg->Show();
$tabControl->begin();
$assets = \Bitrix\Main\Page\Asset::getInstance();
$assets->addJs('/bitrix/js/'.$curModuleName.'/script.js');
?>
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.cards.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.responsive.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.containers.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.tables.min.css">

<form
        method="post"
        action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>&<?=$tabControl->ActiveTabParam()?>"
        enctype="multipart/form-data"
        name="editform"
        class="editform"
>
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    ?>
<!--    <tr class="heading">-->
<!--        <td colspan="2"><b>--><?//echo GetMessage("D2F_COMPRESS_HEADER_SETTINGS")?><!--</b></td>-->
<!--    </tr>-->
    <tr>
        <td width="40%">
            <label for="logic_subdomain">
                <?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ALGORITM")?>:
            </label>
        </td>
        <td width="60%">
            <select name="logic_subdomain">
				<?
				$logicSubdomain = Option::get($curModuleName, "logic_subdomain", 'virtual');
				$logicSubdomainList = [
                    'virtual' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_VIRTUAL"),
                    'subdomain' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_SUBDOMAIN").' (sub.site.ru)',
                    'directory' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_DIRECTORY").' (site.ru/sub/)',
                ];
				foreach($logicSubdomainList as $k=>$v){ ?>
                    <option
                            value="<?=$k?>"
                            <?=($k==$logicSubdomain?'selected':'')?>
                            <?=($k=='directory')?'disabled':''?>
                    >
                        <?=$v?></option>
				<? } ?>
            </select>
        </td>
    </tr>

    <tr>
        <td width="40%">
            <label for="type_subdomain">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_TYPE")?>:
            </label>
        </td>
        <td width="60%">
            <select name="type_subdomain">
				<?
				$typeSubdomain = Option::get($curModuleName, "type_subdomain", 'country');
				$typeSubdomainList = [
					'city' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_CITY"),
					'country' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_COUNTRY"),
				];
				foreach($typeSubdomainList as $k=>$v){ ?>
                    <option value="<?=$k?>" <?=($k==$typeSubdomain?'selected':'')?>><?=$v?></option>
				<? } ?>
            </select>
        </td>
    </tr>

    <tr>
        <td width="40%"></td>
        <td width="60%">
            <i><?=Loc::getMessage("D2F_MULTIDOMAIN_DESCRIPTION_TYPE")?></i>
        </td>
    </tr>

    <tr>
        <td width="40%">
            <label for="key_ip">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_IP")?>:
            </label>
        </td>
        <td width="60%">
            <select name="key_ip">
				<?
				$keyIp = Option::get($curModuleName, "key_ip", 'HTTP_X_REAL_IP');
				$keyIpList = [
					'HTTP_X_REAL_IP' => 'HTTP_X_REAL_IP (IP:'.$_SERVER['HTTP_X_REAL_IP'].')',
					'REMOTE_ADDR' => 'REMOTE_ADDR (IP:'.$_SERVER['REMOTE_ADDR'].')',
				];
				foreach($keyIpList as $k=>$v){ ?>
                    <option value="<?=$k?>" <?=($k==$keyIp?'selected':'')?>><?=$v?></option>
				<? } ?>
            </select>
        </td>
    </tr>

    <tr>
        <td width="40%">
            <label for="lang_default">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_DOMAIN_DEFAULT")?>:
            </label>
        </td>
        <td width="60%">
            <input type="text"
                   name="domain_default"
                   value="<?=Option::get($curModuleName, "domain_default",$_SERVER['HTTP_HOST'])?>"
            />
            <br>
            <i><?=Loc::getMessage("D2F_MULTIDOMAIN_DESCRIPTION_DOMAIN_DEFAULT")?></i>
        </td>
    </tr>

    <tr>
        <td width="40%">
            <label>
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_MAPPING_LIST")?>:
            </label>
        </td>
        <td width="60%">
            <table class="nopadding" cellpadding="0" cellspacing="0" border="0" width="100%" id="d2f_mapping_list">
                <tbody>
                    <?
                    $subdomainList = Option::get($curModuleName, "mapping_list");
					$lastKey = 0;
                    if($subdomainList) {
						$subdomainList = unserialize($subdomainList);
                        foreach($subdomainList as $k=>$v) {
//                            $k = str_replace('n','',$k);
                            ?>
                            <tr>
                                <td>
                                    <input type="text"
                                           size="50"
                                           name="MAPLIST[n<?=$lastKey?>][KEY]"
                                           value="<?=(isset($v['KEY'])?$v['KEY']:'')?>"
                                           placeholder="<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_MAPPING_LIST_KEY")?>"
                                    />
                                    <input type="text"
                                           size="50"
                                           name="MAPLIST[n<?=$lastKey?>][SUBNAME]"
                                           value="<?=(isset($v['SUBNAME'])?$v['SUBNAME']:'')?>"
                                           placeholder="<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_MAPPING_LIST_SUBNAME")?>"
                                    />
                                </td>
                            </tr>
                        <? $lastKey++; } ?>
                    <? } ?>
                    <tr>
                        <td>
                            <input type="text"
                                   size="50"
                                   name="MAPLIST[n<?=$lastKey?>][KEY]"
                                   value=""
                                   placeholder="<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_MAPPING_LIST_KEY")?>"
                            />
                            <input type="text"
                                   size="50"
                                   name="MAPLIST[n<?=$lastKey?>][SUBNAME]"
                                   value=""
                                   placeholder="<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_MAPPING_LIST_SUBNAME")?>"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="button" value="<?=Loc::getMessage("LABEL_ADD");?>" onclick="addNewRow('d2f_mapping_list')">
                        </td>
                    </tr>
                    <script type="text/javascript">
                        // BX.addCustomEvent('onAutoSaveRestore', function(ob, data) {
                        //     for (var i in data){
                        //         if (i.substring(0,9)=='SUBLIST['){
                        //             addNewRow('d2f_subdomain_list')
                        //         }
                        //     }
                        // });
                    </script>
                </tbody>
            </table>
        </td>
    </tr>


    <tr>
        <td width="40%">
            <label for="path_to_optipng">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_EXCLUDE_PATH")?>:
            </label>
        </td>
        <td width="60%">
            <table class="nopadding" cellpadding="0" cellspacing="0" border="0" width="100%" id="d2f_exclude_path">
                <tbody>
				<?
				$excludeList = Option::get($curModuleName, "exclude_path");
				$lastKey = 0;
				if($excludeList) {
					$excludeList = unserialize($excludeList);
					foreach($excludeList as $k=>$v) {
//						$k = str_replace('n','',$k);
						?>
                        <tr>
                            <td>
                                <input type="text"
                                       size="80"
                                       name="EXCLUDE_PATH[n<?=$lastKey++;?>]"
                                       value="<?=$v?>"
                                       placeholder="<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_EXCLUDE_PATH_REG")?>"
                                />
                            </td>
                        </tr>
					<? } ?>
				<? } ?>
                <tr>
                    <td>
                        <input type="text"
                               size="80"
                               name="EXCLUDE_PATH[n<?=$lastKey?>]"
                               value=""
                               placeholder="<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_EXCLUDE_PATH_REG")?>"
                        />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="button" value="<?=GetMessage("LABEL_ADD");?>" onclick="addNewRow('d2f_exclude_path')">
                    </td>
                </tr>
                <script type="text/javascript">
                    BX.addCustomEvent('onAutoSaveRestore', function(ob, data) {
                        for (var i in data){
                            if (i.substring(0,9)=='EXCLUDE_PATH['){
                                addNewRow('d2f_exclude_path')
                            }
                        }
                    });
                </script>
                </tbody>
            </table>
        </td>
    </tr>


	<?
	$tabControl->beginNextTab();
	?>
    <tr>
        <td width="40%">
            <label for="enable_multilang">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_MULTILANG")?>:
            </label>
        </td>
        <td width="60%">
            <input type="checkbox"
                   name="enable_multilang"
                   value="Y"
				<?
				if(Option::get($curModuleName, "enable_multilang")=='Y') {
					echo 'checked';
				}
				?>
            />
        </td>
    </tr>

    <tr>
        <td width="40%">
            <label for="lang_default">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_LANG_DEFAULT")?>:
            </label>
        </td>
        <td width="60%">
            <input type="text"
                   name="lang_default"
                   value="<?=Option::get($curModuleName, "lang_default",'ru')?>"
            />
        </td>
    </tr>

    <?
	$tabControl->beginNextTab();
	// DOMAINS TAB
    ?>
    <h2><?=Loc::getMessage("D2F_MULTIDOMAIN_DOMAIN_LIST_H2")?></h2>
    <?
	echo BeginNote();
	echo Loc::getMessage("D2F_MULTIDOMAIN_SUBDOMAIN_LIST_NOTE",['#ID#'=>Option::get($curModuleName, "highload_domains")]);
	EndNote();
    ?>

	<?
	$tabControl->beginNextTab();
	// SEO TAB
	?>
    <tr>
        <td width="40%">
            <label for="enable_seo_page">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_SEO_PAGE")?>:
            </label>
        </td>
        <td width="60%">
            <input type="checkbox"
                   name="enable_seo_page"
                   value="Y"
				<?
				if(Option::get($curModuleName, "enable_seo_page")=='Y') {
					echo 'checked';
				}
				?>
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="enable_seo_title_add_city">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_SEO_TITLE_ADD_CITY")?>:
            </label>
        </td>
        <td width="60%">
            <input type="checkbox"
                   name="enable_seo_title_add_city"
                   value="Y"
				<?
				if(Option::get($curModuleName, "enable_seo_title_add_city")=='Y') {
					echo 'checked';
				}
				?>
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="pattern_seo_title_add_city">
				<?=Loc::getMessage("D2F_MULTIDOMAIN_LABEL_PATTERN_SEO_TITLE_ADD_CITY")?>:
            </label>
        </td>
        <td width="60%">
            <input type="text"
                   name="pattern_seo_title_add_city"
                   value="<?=Option::get($curModuleName, "pattern_seo_title_add_city","#TITLE# - #CITY#")?>"
            />
        </td>
    </tr>
	<?$tabControl->BeginNextTab();?>
    <tr>
        <td colspan="2" align="left">
            <div class="o-container--super">
                <div class="o-grid">
                    <div class="o-grid__cell o-grid__cell--width-70">
                        <div class="c-card">
                            <div class="c-card__body">
                                <p class="c-paragraph"><?= Loc::getMessage('LABEL_TITLE_HELP_BEGIN')?>.</p>
								<?=Loc::getMessage('LABEL_TITLE_HELP_BEGIN_TEXT');?>
                            </div>
                        </div>
                        <div class="o-container--large">
                            <h2 id="yaPay" class="c-heading u-large"><?=Loc::getMessage('LABEL_TITLE_HELP_DONATE_TEXT');?></h2>
                            <iframe src="https://money.yandex.ru/quickpay/shop-widget?writer=seller&targets=%D0%9F%D0%BE%D0%B4%D0%B4%D0%B5%D1%80%D0%B6%D0%BA%D0%B0%20%D0%BE%D0%B1%D0%BD%D0%BE%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9%20%D0%B1%D0%B5%D1%81%D0%BF%D0%BB%D0%B0%D1%82%D0%BD%D1%8B%D1%85%20%D0%BC%D0%BE%D0%B4%D1%83%D0%BB%D0%B5%D0%B9&targets-hint=&default-sum=500&button-text=14&payment-type-choice=on&mobile-payment-type-choice=on&hint=&successURL=&quickpay=shop&account=410011413398643" width="450" height="228" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
                            <h2 id="morePay" class="c-heading u-large"><?=Loc::getMessage('LABEL_TITLE_HELP_DONATE_ALL_TEXT');?></h2>
                            <table class="c-table">
                                <tbody class="c-table__body c-table--striped">
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Yandex.Money</td>
                                    <td class="c-table__cell">410011413398643</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMR (rub)</td>
                                    <td class="c-table__cell">R218843696478</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMU (uah)</td>
                                    <td class="c-table__cell">U135571355496</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMZ (usd)</td>
                                    <td class="c-table__cell">Z418373807413</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WME (euro)</td>
                                    <td class="c-table__cell">E331660539346</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMX (btc)</td>
                                    <td class="c-table__cell">X740165207511</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WML (ltc)</td>
                                    <td class="c-table__cell">L718094223715</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMH (bch)</td>
                                    <td class="c-table__cell">H526457512792</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">PayPal</td>
                                    <td class="c-table__cell"><a href="https://www.paypal.me/darkfriend" target="_blank">paypal.me/@darkfriend</a></td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Payeer</td>
                                    <td class="c-table__cell">P93175651</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Bitcoin</td>
                                    <td class="c-table__cell">15Veahdvoqg3AFx3FvvKL4KEfZb6xZiM6n</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Litecoin</td>
                                    <td class="c-table__cell">LRN5cssgwrGWMnQruumfV2V7wySoRu7A5t</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Ethereum</td>
                                    <td class="c-table__cell">0xe287Ac7150a087e582ab223532928a89c7A7E7B2</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">BitcoinCash</td>
                                    <td class="c-table__cell">bitcoincash:qrl8p6jxgpkeupmvyukg6mnkeafs9fl5dszft9fw9w</td>
                                </tr>
                                </tbody>
                            </table>
                            <h2 id="moreThanks" class="c-heading u-large"><?=Loc::getMessage('LABEL_TITLE_HELP_DONATE_OTHER_TEXT');?></h2>
							<?=Loc::getMessage('LABEL_TITLE_HELP_DONATE_OTHER_TEXT_S');?>
                        </div>
                    </div>
                    <div class="o-grid__cell o-grid__cell--width-30">
                        <h2 id="moreThanks" class="c-heading u-large"><?=Loc::getMessage('LABEL_TITLE_HELP_DONATE_FOLLOW');?></h2>
                        <table class="c-table">
                            <tbody class="c-table__body">
                            <tr class="c-table__row">
                                <td class="c-table__cell">
                                    <a href="https://vk.com/dev2fun" target="_blank">vk.com/dev2fun</a>
                                </td>
                            </tr>
                            <tr class="c-table__row">
                                <td class="c-table__cell">
                                    <a href="https://facebook.com/dev2fun" target="_blank">facebook.com/dev2fun</a>
                                </td>
                            </tr>
                            <tr class="c-table__row">
                                <td class="c-table__cell">
                                    <a href="https://twitter.com/dev2fun" target="_blank">twitter.com/dev2fun</a>
                                </td>
                            </tr>
                            <tr class="c-table__row">
                                <td class="c-table__cell">
                                    <a href="https://t.me/dev2fun" target="_blank">telegram/dev2fun</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <?php
    $tabControl->Buttons(array(
		"btnSave"=>true,
		"btnApply"=>true,
		"btnCancel"=>true,
		"back_url" => $APPLICATION->GetCurUri(),
	));
    ?>
<!--    <input type="submit"-->
<!--           name="save"-->
<!--           value="--><?//=Loc::getMessage("MAIN_SAVE") ?><!--"-->
<!--           title="--><?//=Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?><!--"-->
<!--           class="adm-btn-save"-->
<!--    />-->
<!--    <input type="submit"-->
<!--           name="test_module"-->
<!--           value="--><?//=Loc::getMessage("D2F_COMPRESS_REFERENCES_TEST_BTN") ?><!--"-->
<!--    />-->
    <? $tabControl->End(); ?>
</form>