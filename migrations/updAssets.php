<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 13.02.2021
 * Time: 2:07
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule('dev2fun.multidomain');
\Bitrix\Main\Loader::registerAutoLoadClasses(
    "dev2fun.multidomain",
    [
        'Dev2fun\MultiDomain\Base' => 'include.php',
    ]
);

$moduleId = \Dev2fun\MultiDomain\Base::$module_id;

DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $moduleId.'/vue');

// copy vue-js files
if (!CopyDirFiles(__DIR__ . "/frontend/dist", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $moduleId.'/vue', true, true)) {
    throw new Exception('ERRORS_SAVE_FILE '.$_SERVER['DOCUMENT_ROOT'] . 'bitrix/js/' . $moduleId.'/vue');
}


\Dev2fun\MultiDomain\Base::ShowThanksNotice();

die("update - Success");