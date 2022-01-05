<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 0.2.3
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule('dev2fun.multidomain');
\Bitrix\Main\Loader::registerAutoLoadClasses(
    "dev2fun.multidomain",
    array(
        'Dev2fun\MultiDomain\Base' => 'include.php',
        'Dev2fun\MultiDomain\SubDomain' => 'classes/general/SubDomain.php',
        'Dev2fun\MultiDomain\Seo' => 'classes/general/Seo.php',
        'Dev2fun\MultiDomain\Geo' => 'classes/general/Geo.php',
        'Dev2fun\MultiDomain\HLHelpers' => 'lib/HLHelpers.php',
        'Dev2fun\MultiDomain\Config' => 'classes/general/Config.php',
        'Dev2fun\MultiDomain\TemplateSeo' => 'classes/general/TemplateSeo.php',
        'Dev2fun\MultiDomain\TabOptions' => 'classes/general/TabOptions.php',
        'Dev2fun\MultiDomain\LangData' => 'classes/general/LangData.php',
    )
);

$moduleId = \Dev2fun\MultiDomain\Base::$module_id;

// path to module dev2fun.multidomain
$pathToModule = $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/dev2fun.multidomain';

// copy vue-js files
if (!CopyDirFiles($pathToModule . "/frontend/dist", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $moduleId.'/vue', true, true)) {
    throw new Exception('ERRORS_SAVE_FILE '.$_SERVER['DOCUMENT_ROOT'] . 'bitrix/js/' . $moduleId.'/vue');
}

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->registerEventHandler("iblock", "OnTemplateGetFunctionClass", $moduleId, "Dev2fun\\MultiDomain\\TemplateSeo", "EventHandler");


\Dev2fun\MultiDomain\Base::ShowThanksNotice();

die("0.2.3 - Success");
