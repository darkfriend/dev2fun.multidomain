<?
/**
* @author dev2fun (darkfriend)
* @copyright darkfriend
* @version 0.1.1
*/
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

CModule::IncludeModule("main");

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option;
?>
<form action="<?= $APPLICATION->GetCurPageParam('UNSTEP=2',array('UNSTEP'))?>" method="post">
    <?= bitrix_sessid_post()?>
    <table width="400" border="0" class="table">
        <tr>
            <td>
                <label for="SAVE_DATA"><?= Loc::getMessage('D2F_MULTIDOMAIN_UNSTEP0_LABEL')?>:</label>
            </td>
            <td>
                <input type="checkbox" name="SAVE_DATA" value="Y" checked>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="hidden" name="UNSTEP" value="2">
                <input type="submit" name="save" value="<?= Loc::getMessage('D2F_MULTIDOMAIN_UNSTEP0_GOTO')?>">
            </td>
        </tr>
    </table>
</form>