<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
if(!check_bitrix_sessid()) return;

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

Loader::includeModule('main');

CAdminMessage::ShowMessage(array(
    "MESSAGE" => $GLOBALS['D2F_MULTIDOMAIN_ERROR'],
//    "MESSAGE" => Loc::getMessage("D2F_MULTIDOMAIN_ERROR"),
    "TYPE" => "ERROR"
));
echo BeginNote();
//echo $GLOBALS['D2F_MULTIDOMAIN_ERROR_NOTES'];
echo Loc::getMessage('D2F_MULTIDOMAIN_UNINSTALL_ERROR_NOTES');
echo EndNote();
