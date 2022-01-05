<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
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

$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
$config = \Dev2fun\MultiDomain\Config::getInstance();

$hlId = \Bitrix\Main\Config\Option::get($moduleId, 'highload_domains_seo');
$field = \CUserTypeEntity::GetList([],['ENTITY_ID'=>'HLBLOCK_' . $hlId,'FIELD_NAME'=>'UF_TITLE'])->GetNext();
if(!empty($field['ID'])) {
    $oUserTypeEntity = new \CUserTypeEntity();
    $arFields['MANDATORY'] = 'N';
    if(!$oUserTypeEntity->Update($field['ID'], $arFields)) {
        throw new Exception('Error save HLBLOCK_' . $hlId);
    }
}

$arSite = \CSite::GetList(
    $by='sort',
    $order='desc',
    [
        'ABS_DOC_ROOT' => $_SERVER['DOCUMENT_ROOT'],
    ]
)->Fetch();
if ($arSite) {
    $currentSite = $arSite['LID'];
} else {
    $currentSite = 's1';
}

$arHl = [
    'highload_domains',
    'highload_domains_seo',
    'lang_fields',
    'lang_data',
];
foreach ($arHl as $item) {
    $hlId = \Bitrix\Main\Config\Option::get($moduleId, $item);
    if ($hlId) {
        $hl->addField($hlId, [
            'FIELD_NAME' => 'UF_SITE_ID',
            'USER_TYPE_ID' => 'string',
            'SORT' => '50',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'EDIT_FORM_LABEL' => [
                'ru' => 'SITE_ID',
                'en' => 'SITE_ID',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'SITE_ID',
                'en' => 'SITE_ID',
            ],
        ]);

        $elements = $hl->getElementList($hlId);
        if($elements) {
            foreach ($elements as $element) {
                $hl->updateElement($hlId, $element['ID'], ['UF_SITE_ID'=>$currentSite]);
            }
        }
    }
}

$options = \Bitrix\Main\Config\Option::getForModule($moduleId);
if($options) {
    foreach ($options as $key => $option) {
        \Bitrix\Main\Config\Option::set(
            $moduleId,
            $key,
            $option,
            $currentSite
        );
    }
}

\Dev2fun\MultiDomain\Base::ShowThanksNotice();

die("1.0.0 - Success");
