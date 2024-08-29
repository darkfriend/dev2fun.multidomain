<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 1.2.0
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule('dev2fun.multidomain');

$moduleId = \Dev2fun\MultiDomain\Base::$module_id;

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->registerEventHandler("main", "OnProlog", $moduleId, "Dev2fun\\MultiDomain\\Base", "OnProlog");

// remove old vue-js files
DeleteDirFilesEx("{$_SERVER["DOCUMENT_ROOT"]}/bitrix/js/{$moduleId}/vue");

// path to module dev2fun.multidomain
$pathToModule = $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/dev2fun.multidomain';
if (!CopyDirFiles("{$pathToModule}/frontend/dist", "{$_SERVER["DOCUMENT_ROOT"]}/bitrix/js/{$moduleId}/vue", true, true)) {
    throw new Exception("ERRORS_SAVE_FILE {$_SERVER['DOCUMENT_ROOT']}/bitrix/js/{$moduleId}/vue");
}

\Dev2fun\MultiDomain\Base::ShowThanksNotice();

die("1.2.0 - Success");
