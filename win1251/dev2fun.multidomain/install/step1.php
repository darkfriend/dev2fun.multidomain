<?
/**
* 
* @author dev2fun (darkfriend)
* @copyright darkfriend
* @version 0.1.1
* 
*/
if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

CModule::IncludeModule("main");

$msg = new CAdminMessage([
	'MESSAGE' => GetMessage("D2F_MULTIDOMAIN_INSTALL_SUCCESS"),
	'TYPE' => 'OK',
]);
echo $msg->Show();

echo BeginNote();
echo GetMessage("D2F_MULTIDOMAIN_INSTALL_LAST_MSG");
EndNote();