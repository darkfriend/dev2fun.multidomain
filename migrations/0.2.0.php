<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 0.2.0
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule('highloadblock');
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

// copy vue-js files
if (!CopyDirFiles(__DIR__ . "/frontend/dist", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $moduleId.'/vue', true, true)) {
    throw new Exception('ERRORS_SAVE_FILE '.$_SERVER['DOCUMENT_ROOT'] . 'bitrix/js/' . $moduleId.'/vue');
}

$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();

if (!\Bitrix\Main\Config\Option::get($moduleId, 'lang_fields')) {
    $hlId = $hl->create('Dev2funMultiDomainLangFields', 'dev2fun_multidomain_lang_fields');
    if (!$hlId) {
        throw new Exception(\Dev2fun\MultiDomain\HLHelpers::$LAST_ERROR);
    }
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_IBLOCK_ID',
        'USER_TYPE_ID' => 'string',
        'SORT' => '100',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'Y',
    ]);
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_FIELD',
        'USER_TYPE_ID' => 'string',
        'SORT' => '200',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'Y',
    ]);
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_FIELD_TYPE',
        'USER_TYPE_ID' => 'string',
        'SORT' => '300',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'Y',
    ]);
    if (!$hlId) throw new Exception(\Dev2fun\MultiDomain\HLHelpers::$LAST_ERROR);
    \Bitrix\Main\Config\Option::set($moduleId, 'lang_fields', $hlId);
}

if (!\Bitrix\Main\Config\Option::get($moduleId, 'lang_data')) {
    $hlId = $hl->create('Dev2funMultiDomainLangData', 'dev2fun_multidomain_lang_data');
    if (!$hlId) {
        throw new Exception(\Dev2fun\MultiDomain\HLHelpers::$LAST_ERROR);
    }
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_FIELD_ID',
        'USER_TYPE_ID' => 'integer',
        'SORT' => '100',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'Y',
    ]);
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_DOMAIN_ID',
        'USER_TYPE_ID' => 'integer',
        'SORT' => '200',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'Y',
    ]);
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_ELEMENT_ID',
        'USER_TYPE_ID' => 'integer',
        'SORT' => '300',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'Y',
    ]);
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_REF_TYPE',
        'USER_TYPE_ID' => 'string',
        'SORT' => '400',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
    ]);
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_VALUE_TYPE',
        'USER_TYPE_ID' => 'string',
        'SORT' => '500',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
    ]);
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_VALUE_STRING',
        'USER_TYPE_ID' => 'string',
        'SORT' => '600',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
    ]);
    $hl->addField($hlId, [
        'FIELD_NAME' => 'UF_VALUE_TEXT',
        'USER_TYPE_ID' => 'text',
        'SORT' => '700',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SETTINGS' => [
            'ROWS' => '10',
        ],
    ]);
    if (!$hlId) throw new Exception(\Dev2fun\MultiDomain\HLHelpers::$LAST_ERROR);
    \Bitrix\Main\Config\Option::set($moduleId, 'lang_data', $hlId);
}

$eventManager = \Bitrix\Main\EventManager::getInstance();

//tab
$eventManager->registerEventHandler("main", "OnAdminTabControlBegin", $moduleId, "Dev2fun\\MultiDomain\\Base", "AddAdminLangTab");

// element
$eventManager->registerEventHandler("iblock", "OnBeforeIBlockElementAdd", $moduleId, "Dev2fun\\MultiDomain\\Base", "OnBeforeIBlockElementUpdate");
$eventManager->registerEventHandler("iblock", "OnBeforeIBlockElementUpdate", $moduleId, "Dev2fun\\MultiDomain\\Base", "OnBeforeIBlockElementUpdate");
$eventManager->registerEventHandler("iblock", "OnAfterIBlockElementDelete", $moduleId, "Dev2fun\\MultiDomain\\Base", "OnAfterIBlockElementDelete");

// section
$eventManager->registerEventHandler("iblock", "OnAfterIBlockSectionAdd", $moduleId, "Dev2fun\\MultiDomain\\Base", "OnAfterIBlockSectionEvent");
$eventManager->registerEventHandler("iblock", "OnAfterIBlockSectionUpdate", $moduleId, "Dev2fun\\MultiDomain\\Base", "OnAfterIBlockSectionEvent");
$eventManager->registerEventHandler("iblock", "OnAfterIBlockSectionDelete", $moduleId, "Dev2fun\\MultiDomain\\Base", "OnAfterIBlockSectionDelete");


\Dev2fun\MultiDomain\Base::ShowThanksNotice();

die("0.2.0 - Success");