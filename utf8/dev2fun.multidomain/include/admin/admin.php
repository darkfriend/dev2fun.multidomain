<?php
/**
 * @author dev2fun <darkfriend>
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 0.2.0
 */

use Dev2fun\MultiDomain\Config;

\CJSCore::Init(array('jquery'));
$asset = \Bitrix\Main\Page\Asset::getInstance();
$asset->addString('<script>'.\file_get_contents(__DIR__.'/script.js').'</script>', \Bitrix\Main\Page\AssetLocation::BODY_END);
?>
<style>
    <?=include __DIR__.'/style.css'?>
</style>
<?php
$config = Config::getInstance();
$hlDomains = $config->get('highload_domains');
$hl = \Darkfriend\HLHelpers::getInstance();
$domains = $hl->getElementList($hlDomains,[
    '!=UF_NAME' => 'main',
    'UF_ACTIVE' => 1,
]);
$tabOptions = \Dev2fun\MultiDomain\TabOptions::getInstance();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
?>

<table class="adm-detail-content-table edit-table">
    <tr>
        <td>
            <?php if(!$domains) { ?>
                <p><?=\Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_DOMAIN_NOT_FOUNDS',['#URL#'=>'/bitrix/admin/settings.php?lang=ru&mid=dev2fun.multidomain&mid_menu=1&vue=1#editDomains'])?></p>
            <?php } else { ?>
                <div class="tabs-container">
                    <div class="tabs-right">
                        <ul class="nav nav-tabs">
                            <?php foreach($domains as $k=>$item) { ?>
                                <li>
                                    <a
                                        class="nav-link <?=($k==0)?'active':''?>"
                                        data-toggle="tab"
                                        href="#tablang-<?=$item['ID']?>"
                                        data-id="<?=$item['ID']?>"
                                    > <?=$item['UF_NAME']?> (<?=$item['UF_LANG']?>)</a>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="tab-content">
                            <?php foreach($domains as $k=>$item) { ?>
                                <input type="hidden" name="Dev2funLang[<?=$k?>][DOMAIN_ID]" value="<?=$item['ID']?>">
                                <div id="tablang-<?=$item['ID']?>" class="tab-pane <?=($k==0)?'active':''?>">
                                    <div class="panel-body">
                                        <div class="adm-detail-title">Domain: <?=$tabOptions->getDomainName($item)?></div>
                                        <br>

                                        <table class="adm-detail-content-table edit-table">
                                            <?php
                                            $res = $tabOptions->showElements($item, $k);
                                            if(!$res) {
                                                echo \Bitrix\Main\Localization\Loc::getMessage('D2F_MULTIDOMAIN_FIELDS_NOT_FOUNDS',['#URL#'=>'/bitrix/admin/settings.php?lang=ru&mid=dev2fun.multidomain&mid_menu=1&vue=1#editMultilang']);
                                                echo '';
                                            }
                                            ?>
                                        </table>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
            <?php } ?>
        </td>
    </tr>
</table>


