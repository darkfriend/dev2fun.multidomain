<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 1.1.9
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

// remove old vue-js files
DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $moduleId.'/vue');

// copy vue-js files
if (!CopyDirFiles($pathToModule . "/frontend/dist", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $moduleId.'/vue', true, true)) {
    throw new Exception('ERRORS_SAVE_FILE '.$_SERVER['DOCUMENT_ROOT'] . 'bitrix/js/' . $moduleId.'/vue');
}

\Dev2fun\MultiDomain\Base::ShowThanksNotice();

die("1.1.9 - Success");
