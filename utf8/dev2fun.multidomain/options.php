<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.1.9
 */

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Dev2fun\MultiDomain\Config;

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}
$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();
$curModuleName = "dev2fun.multidomain";
//Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

include_once __DIR__ . '/classes/composer/vendor/autoload.php';

\Bitrix\Main\Loader::includeModule('iblock');

if ($request->isPost() && check_bitrix_sessid()) {
    $result = [
        'success' => false,
        'msg' => '',
        'data' => [],
    ];
    try {
        $config = Config::getInstance();
        $hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
        $siteId = $request->getPost('siteId');
        if(!$siteId) {
            throw new \Exception('SiteId is wrong!');
        }
        switch ($request->getPost('action')) {
            case 'get':
                $mappingList = $config->get("mapping_list", [['KEY' => '', 'SUBNAME' => '']], $siteId);
                $excludeList = $config->get("exclude_path", ['\/(bitrix|local)\/(admin|tools)\/'], $siteId);
                $langFields = $hl->getElementList($config->getCommon('lang_fields', ''));
                if ($langFields) {
                    foreach ($langFields as &$langField) {
                        $langField = [
                            'iblock' => $langField['UF_IBLOCK_ID'],
                            'field' => $langField['UF_FIELD'],
                            'fieldType' => $langField['UF_FIELD_TYPE'],
                        ];
                    }
                    unset($langField);
                }
                $paramsObject = [
                    'enable' => $config->get("enable", 'N', $siteId) === 'Y',
                    'logic_subdomain' => $config->get("logic_subdomain", \Dev2fun\MultiDomain\SubDomain::LOGIC_DIRECTORY, $siteId),
                    'type_subdomain' => $config->get("type_subdomain", 'country', $siteId),
                    'enable_replace_links' => $config->get("enable_replace_links", 'N', $siteId) === 'Y',
                    'auto_rewrite' => $config->get("auto_rewrite", 'N', $siteId) === 'Y',
                    'key_ip' => $config->get("key_ip", 'REMOTE_ADDR', $siteId),
                    'domain_default' => $config->get("domain_default", $_SERVER['HTTP_HOST'], $siteId),
                    'MAPLIST' => $mappingList,
                    'EXCLUDE_PATH' => $excludeList,

                    'enable_multilang' => $config->get("enable_multilang", 'N', $siteId) === 'Y',
                    'enable_hreflang' => $config->get("enable_hreflang", 'N', $siteId) === 'Y',
                    'lang_default' => $config->get("lang_default", 'ru', $siteId),
                    'lang_fields' => $langFields,

                    'enable_seo_page' => $config->get("enable_seo_page", 'N', $siteId) === 'Y',
                ];
                $result['data'] = $paramsObject;
                break;
            case 'save':
                $arFields = [];
                $arCheckbox = [];

                $arCheckbox['enable'] = $request->getPost('enable');
                $arFields['logic_subdomain'] = $request->getPost('logic_subdomain');
                $arFields['type_subdomain'] = $request->getPost('type_subdomain');
                $arFields['key_ip'] = $request->getPost('key_ip');
                $arFields['domain_default'] = $request->getPost('domain_default');
                $arFields['enable_replace_links'] = $request->getPost('enable_replace_links');

                $arCheckbox['enable_hreflang'] = $request->getPost('enable_hreflang');
                $arCheckbox['auto_rewrite'] = $request->getPost('auto_rewrite');

                // seo tab
                $arCheckbox['enable_seo_page'] = $request->getPost('enable_seo_page');
                $arCheckbox['enable_seo_title_add_city'] = $request->getPost('enable_seo_title_add_city');

                $maplist = $request->getPost('MAPLIST');
                if ($maplist) {
                    foreach ($maplist as $k => $v) {
                        if (!$v['KEY'] || !$v['SUBNAME']) {
                            unset($maplist[$k]);
                        }
                    }
                    if ($maplist) {
                        $maplist = \serialize($maplist);
                    } else {
                        $maplist = '';
                    }
                    $arFields['mapping_list'] = $maplist;
                }
                $exlist = $request->getPost('EXCLUDE_PATH');
                if ($exlist) {
                    foreach ($exlist as $k => $v) {
                        if (!$v) {
                            unset($exlist[$k]);
                        }
                    }
                    if ($exlist) {
                        $exlist = \serialize($exlist);
                    } else {
                        $exlist = '';
                    }
                    $arFields['exclude_path'] = $exlist;
                }
                $arFields['enable_multilang'] = $request->getPost('enable_multilang');
                $arFields['lang_default'] = $request->getPost('lang_default');

                foreach ($arFields as $k => $arField) {
                    $config->set($k, $arField, $siteId);
                }

                if($arCheckbox) {
                    foreach ($arCheckbox as $k => $arField) {
                        $config->set($k, $arField==='Y' ? 'Y' : 'N', $siteId);
                    }
                }

                $langFields = $request->getPost('lang_fields');
                if ($langFields) {
                    $hl = \Darkfriend\HLHelpers::getInstance();
                    $elements = [];
                    foreach ($langFields as $langField) {
                        if (isset($elements[$langField['iblock']])) {
                            continue;
                        }
                        $elements[$langField['iblock']] = $hl->getElementList(
                            Config::getInstance()->getCommon('lang_fields'),
                            [
                                'UF_SITE_ID' => $siteId,
                                'UF_IBLOCK_ID' => $langField['iblock'],
                            ]
                        );
                    }

                    if ($elements) {
                        foreach ($elements as $k => $elementFields) {
                            foreach ($elementFields as $eKey => $element) {
                                $elements[$element['UF_IBLOCK_ID'] . $element['UF_FIELD_TYPE'] . $element['UF_FIELD']] = $element;
                                unset($elements[$k][$eKey]);
                            }
                        }
                    }

                    $addedFields = [];
                    foreach ($langFields as $langField) {
                        if (isset($elements[$langField['iblock'] . $langField['fieldType'] . $langField['field']])) {
                            unset($elements[$langField['iblock'] . $langField['fieldType'] . $langField['field']]);
                            continue;
                        }
                        if (\in_array($langField['iblock'] . $langField['fieldType'] . $langField['field'], $addedFields)) {
                            continue;
                        }
                        $hl->addElement(
                            Config::getInstance()->getCommon('lang_fields'),
                            [
                                'UF_SITE_ID' => $siteId,
                                'UF_IBLOCK_ID' => $langField['iblock'],
                                'UF_FIELD' => $langField['field'],
                                'UF_FIELD_TYPE' => $langField['fieldType'],
                            ]
                        );
                        $addedFields[] = $langField['iblock'] . $langField['fieldType'] . $langField['field'];
                    }
                    if ($elements) {
                        foreach ($elements as $element) {
                            if (empty($element)) continue;
                            $hl->deleteElement(Config::getInstance()->getCommon('lang_fields'), $element['ID']);
                        }
                    }
                }

                $result['msg'] = 'Настройки успешно сохранены';
                break;
            case 'getIblocks':
                $rsIblocks = CIBlock::GetList(['NAME' => 'ASC'], ['ACTIVE' => 'Y']);
                $result['data']['groups'] = [
                    [
                        'id' => 'iblock',
                        'label' => 'IBlocks',
                    ],
                ];
                while ($iblock = $rsIblocks->GetNext()) {
                    $result['data']['items'][] = [
                        'id' => $iblock['ID'],
                        'label' => "{$iblock['NAME']} [{$iblock['ID']}]",
                        'group' => 'iblock',
                    ];
                }
                $hlIblocks = \Darkfriend\HLHelpers::getInstance()->getList();
                if ($hlIblocks) {
                    $result['data']['groups'][] = [
                        'id' => 'hl',
                        'label' => 'Highload Blocks',
                    ];
                    foreach ($hlIblocks as $hlIblock) {
                        $result['data']['items'][] = [
                            'id' => 'HL' . $hlIblock['ID'],
                            'label' => "{$hlIblock['NAME']}",
                            'group' => 'hl',
                        ];
                    }
                }
                break;
            case 'getFields':
                $id = $request->getPost('id');
                if (\strpos($id, 'HL') === false) {
                    $result['data']['groups'] = [
                        [
                            'id' => 'field',
                            'label' => 'Fields',
                        ],
                        [
                            'id' => 'prop',
                            'label' => 'Properties',
                        ],
                    ];
                    $iblockFields = [
                        'NAME',
                        'PREVIEW_TEXT',
                        'DETAIL_TEXT',
                    ];
                    foreach ($iblockFields as $code) {
                        $result['data']['items'][] = [
                            'id' => $code,
                            'label' => $code,
                            'group' => 'field',
                        ];
                    }
                    $rsIblocks = CIBlock::GetProperties($id, ['NAME' => 'ASC'], ['ACTIVE' => 'Y']);
                    while ($iblock = $rsIblocks->GetNext()) {
                        $result['data']['items'][] = [
                            'id' => $iblock['ID'],
                            'label' => $iblock['NAME'],
                            'group' => 'prop',
                        ];
                    }
                } else {
                    $id = \str_replace('HL', '', $id);
                    $result['data']['groups'][] = [
                        'id' => 'prop',
                        'label' => 'Properties',
                    ];
                    $fields = \Darkfriend\HLHelpers::getInstance()->getFields($id);
                    foreach ($fields as $code => $field) {
                        $result['data']['items'][] = [
                            'id' => $code,
                            'label' => $field->getName(),
                            'group' => 'prop',
                        ];
                    }
                }

                break;
            case 'getFieldsSection':
                $id = $request->getPost('id');
                $result['data']['groups'] = [
                    [
                        'id' => 'field',
                        'label' => 'Fields',
                    ],
                    [
                        'id' => 'prop',
                        'label' => 'Properties',
                    ],
                ];
                $iblockFields = [
                    'NAME',
                    'PICTURE',
                    'DESCRIPTION',
                    'DETAIL_PICTURE',
                ];
                foreach ($iblockFields as $code) {
                    $result['data']['items'][] = [
                        'id' => $code,
                        'label' => $code,
                        'group' => 'field',
                    ];
                }
                $rsData = CUserTypeEntity::GetList(['FIELD_NAME' => 'ASC'], ['ENTITY_ID' => "IBLOCK_{$id}_SECTION"]);
                while ($arField = $rsData->GetNext()) {
                    $result['data']['items'][] = [
                        'id' => $arField['FIELD_NAME'],
                        'label' => $arField['FIELD_NAME'],
                        'group' => 'prop',
                    ];
                }
                break;
            case 'getDomainKeys':
                $typeSubdomain = $request->getPost('typeSubdomain');
                if($typeSubdomain === \Dev2fun\MultiDomain\SubDomain::TYPE_LANG) {
                    break;
                }
                break;
            case 'updateUrlrewrite':
                $logicSubdomain = $request->getPost('logicSubdomain');
                if($logicSubdomain === \Dev2fun\MultiDomain\SubDomain::LOGIC_DIRECTORY) {
                    \Dev2fun\MultiDomain\UrlRewriter::setAll($siteId);
                } else {
                    \Dev2fun\MultiDomain\UrlRewriter::removeAll($siteId);
                }
                $result['data'] = Loc::getMessage("D2F_MULTIDOMAIN_TEXT_SAVED_URLREWRITE");
                break;
        }


        $result['success'] = true;
    } catch (\Exception $e) {
        $result['msg'] = Loc::getMessage("D2F_MULTIDOMAIN_ERROR_SAVED_SETTINGS");
    }

    $APPLICATION->RestartBuffer();
    \darkfriend\helpers\Response::json($result, [
        'show' => true,
        'die' => true,
    ]);
}
$msg = new CAdminMessage([
    'MESSAGE' => Loc::getMessage("D2F_MULTIDOMAIN_DONATE_MESSAGES", ['#URL#' => '/bitrix/admin/settings.php?lang=ru&mid=dev2fun.multidomain&mid_menu=1&tabControl_active_tab=donate']),
    'TYPE' => 'OK',
    'HTML' => true,
]);
echo $msg->Show();

$assets = \Bitrix\Main\Page\Asset::getInstance();
$assets->addJs('/bitrix/js/' . $curModuleName . '/script.js');
?>

<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.cards.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.responsive.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.containers.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.tables.min.css">

<?php
$staticVersion = include __DIR__.'/include/staticVersion.php';
$vueScripts = [
    "/bitrix/js/dev2fun.multidomain/vue/js/main.{$staticVersion}.bundle.js",
    "/bitrix/js/dev2fun.multidomain/vue/js/polyfill.{$staticVersion}.bundle.js",
];
//$vueScripts = [
//    "/bitrix/modules/dev2fun.multidomain/frontend/dist/js/main.{$staticVersion}.bundle.js",
//    "/bitrix/modules/dev2fun.multidomain/frontend/dist/js/polyfill.{$staticVersion}.bundle.js",
//];
foreach ($vueScripts as $script) {
//    echo '<script src="'.$script.'"></script>';
    $assets->addJs($script);
}
$config = Config::getInstance();
$siteId = \Dev2fun\MultiDomain\Site::getDefault();

$mappingList = $config->get("mapping_list", [['KEY' => '', 'SUBNAME' => '']], $siteId);
$excludeList = $config->get("exclude_path", ['\/(bitrix|local)\/(admin|tools)\/'], $siteId);
$hl = \Darkfriend\HLHelpers::getInstance();
$langFields = $hl->getElementList($config->getCommon('lang_fields'));
if ($langFields) {
    foreach ($langFields as &$langField) {
        $langField = [
            'iblock' => $langField['UF_IBLOCK_ID'],
            'field' => $langField['UF_FIELD'],
            'fieldType' => $langField['UF_FIELD_TYPE'],
        ];
    }
    unset($langField);
}
$paramsObject = \CUtil::phpToJSObject([
    'enable' => $config->get("enable", 'N', $siteId) === 'Y',
    'logic_subdomain' => $config->get("logic_subdomain", \Dev2fun\MultiDomain\SubDomain::LOGIC_DIRECTORY, $siteId),
    'type_subdomain' => $config->get("type_subdomain", 'country', $siteId),
    'enable_replace_links' => $config->get("enable_replace_links", 'N', $siteId) === 'Y',
    'auto_rewrite' => $config->get("auto_rewrite", 'N', $siteId) === 'Y',
    'key_ip' => $config->get("key_ip", 'REMOTE_ADDR', $siteId),
    'domain_default' => $config->get("domain_default", $_SERVER['HTTP_HOST'], $siteId),
    'MAPLIST' => $mappingList,
    'EXCLUDE_PATH' => $excludeList,

    'enable_multilang' => $config->get("enable_multilang", 'N', $siteId) === 'Y',
    'enable_hreflang' => $config->get("enable_hreflang", 'N', $siteId) === 'Y',
    'lang_default' => $config->get("lang_default", 'ru', $siteId),
    'lang_fields' => $langFields,

    'enable_seo_page' => $config->get("enable_seo_page", 'N', $siteId) === 'Y',
]);
$settingsObject = \CUtil::phpToJSObject([
    'remoteAddr' => $_SERVER['REMOTE_ADDR'],
    'realIp' => $_SERVER['HTTP_X_REAL_IP'],
]);
$formObject = \CUtil::phpToJSObject([
    'sessid' => bitrix_sessid_val(),
    'action' => \sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), \urlencode($mid), \LANGUAGE_ID),
]);
$localeObject = \CUtil::phpToJSObject([
    'MAIN_TAB_SET' => Loc::getMessage("MAIN_TAB_SET"),
    'D2F_MULTIDOMAIN_MAIN_TAB_SETTINGS' => Loc::getMessage("D2F_MULTIDOMAIN_MAIN_TAB_SETTINGS"),
    'MAIN_TAB_TITLE_SET' => Loc::getMessage("MAIN_TAB_TITLE_SET"),

    'D2F_MULTIDOMAIN_TAB_2' => Loc::getMessage("D2F_MULTIDOMAIN_TAB_2"),
    'D2F_MULTIDOMAIN_TAB_2_TITLE_SET' => Loc::getMessage("D2F_MULTIDOMAIN_TAB_2_TITLE_SET"),

    'D2F_MULTIDOMAIN_TAB_3' => Loc::getMessage("D2F_MULTIDOMAIN_TAB_3"),
    'D2F_MULTIDOMAIN_TAB_3_TITLE_SET' => Loc::getMessage("D2F_MULTIDOMAIN_TAB_3_TITLE_SET"),

    'D2F_MULTIDOMAIN_TAB_4' => Loc::getMessage("D2F_MULTIDOMAIN_TAB_4"),
    'D2F_MULTIDOMAIN_TAB_4_TITLE_SET' => Loc::getMessage("D2F_MULTIDOMAIN_TAB_4_TITLE_SET"),

    'SEC_DONATE_TAB' => Loc::getMessage("SEC_DONATE_TAB"),
    'SEC_DONATE_TAB_TITLE' => Loc::getMessage("SEC_DONATE_TAB_TITLE"),


    'LABEL_ENABLE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE"),
    'LABEL_ALGORITM' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ALGORITM"),
    'LABEL_VIRTUAL' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_VIRTUAL"),
    'LABEL_SUBDOMAIN' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_SUBDOMAIN"),
    'LABEL_DIRECTORY' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_DIRECTORY"),
    'LABEL_STRUCTURE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_STRUCTURE"),

    'LABEL_TYPE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_TYPE"),
    'LABEL_CITY' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_CITY"),
    'LABEL_COUNTRY' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_COUNTRY"),
    'LABEL_TYPE_LANG' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_TYPE_LANG"),

    'LABEL_ENABLE_REPLACE_LINKS' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_REPLACE_LINKS"),
    'LABEL_ENABLE_AUTO_REWRITE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_AUTO_REWRITE"),
    'DESCRIPTION_TYPE' => Loc::getMessage("D2F_MULTIDOMAIN_DESCRIPTION_TYPE"),
    'LABEL_IP' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_IP"),
    'LABEL_DOMAIN_DEFAULT' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_DOMAIN_DEFAULT"),
    'DESCRIPTION_DOMAIN_DEFAULT' => Loc::getMessage("D2F_MULTIDOMAIN_DESCRIPTION_DOMAIN_DEFAULT"),
    'LABEL_MAPPING_LIST' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_MAPPING_LIST"),
    'LABEL_MAPPING_LIST_KEY' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_MAPPING_LIST_KEY"),
    'LABEL_MAPPING_LIST_SUBNAME' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_MAPPING_LIST_SUBNAME"),
    'LABEL_ADD' => Loc::getMessage("LABEL_ADD"),
    'D2F_MULTIDOMAIN_LABEL_DELETE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_DELETE"),
    'D2F_MULTIDOMAIN_PLACEHOLDER_TYPE' => Loc::getMessage("D2F_MULTIDOMAIN_PLACEHOLDER_TYPE"),
    'D2F_MULTIDOMAIN_LABEL_SUPPORT_TRANSLATE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_SUPPORT_TRANSLATE"),

    'LABEL_EXCLUDE_PATH' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_EXCLUDE_PATH"),
    'LABEL_EXCLUDE_PATH_REG' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_EXCLUDE_PATH_REG"),

    'LABEL_URLREWRITE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_URLREWRITE"),
    'LABEL_URLREWRITE_INFO1' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_URLREWRITE_INFO1"),
    'LABEL_URLREWRITE_INFO2' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_URLREWRITE_INFO2"),

    'LABEL_ENABLE_MULTILANG' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_MULTILANG"),
    'LABEL_LANG_DEFAULT' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_LANG_DEFAULT"),
    'LABEL_ENABLE_HREFLANG' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_HREFLANG"),
    'LABEL_LANG_SUPPORT_FIELDS' => 'Поле с поддержкой перевода',

    'D2F_MULTIDOMAIN_LABEL_TAB_SELECT_ALL' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_TAB_SELECT_ALL"),
    'D2F_MULTIDOMAIN_LABEL_TAB_COLLAPSE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_TAB_COLLAPSE"),


    'DOMAIN_LIST_H2' => Loc::getMessage("D2F_MULTIDOMAIN_DOMAIN_LIST_H2"),
    'D2F_MULTIDOMAIN_SUBDOMAIN_LIST_NOTE' => \htmlspecialchars(Loc::getMessage("D2F_MULTIDOMAIN_SUBDOMAIN_LIST_NOTE", [
        '#ID#' => $config->getCommon("highload_domains"),
    ])),
    'LABEL_ENABLE_SEO_PAGE' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_SEO_PAGE"),
    'LABEL_ENABLE_SEO_TITLE_ADD_CITY' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_ENABLE_SEO_TITLE_ADD_CITY"),
    'LABEL_PATTERN_SEO_TITLE_ADD_CITY' => Loc::getMessage("D2F_MULTIDOMAIN_LABEL_PATTERN_SEO_TITLE_ADD_CITY"),
    'TEXT_SEO_PATTERN_INFO' => Loc::getMessage("D2F_MULTIDOMAIN_TEXT_SEO_PATTERN_INFO"),

    // donate
    'LABEL_TITLE_HELP_BEGIN' => \htmlspecialchars(Loc::getMessage("LABEL_TITLE_HELP_BEGIN")),
    'LABEL_TITLE_HELP_BEGIN_TEXT' => \htmlspecialchars(Loc::getMessage("LABEL_TITLE_HELP_BEGIN_TEXT")),
    'LABEL_TITLE_HELP_DONATE_TEXT' => \htmlspecialchars(Loc::getMessage("LABEL_TITLE_HELP_DONATE_TEXT")),
    'LABEL_TITLE_HELP_DONATE_ALL_TEXT' => \htmlspecialchars(Loc::getMessage("LABEL_TITLE_HELP_DONATE_ALL_TEXT")),
    'LABEL_TITLE_HELP_DONATE_OTHER_TEXT' => \htmlspecialchars(Loc::getMessage("LABEL_TITLE_HELP_DONATE_OTHER_TEXT")),
    'LABEL_TITLE_HELP_DONATE_OTHER_TEXT_S' => \htmlspecialchars(Loc::getMessage("LABEL_TITLE_HELP_DONATE_OTHER_TEXT_S")),
    'LABEL_TITLE_HELP_DONATE_FOLLOW' => \htmlspecialchars(Loc::getMessage("LABEL_TITLE_HELP_DONATE_FOLLOW")),
]);
$sitesObject = \CUtil::phpToJSObject(\Dev2fun\MultiDomain\Site::all());
?>
<div id="dev2funMultiDomain">
    <app
        :input-value="<?= $paramsObject ?>"
        :settings="<?= $settingsObject ?>"
        :form-settings="<?= $formObject ?>"
        :locale="<?= $localeObject ?>"
        :sites="<?=$sitesObject?>"
        :site-default="'<?=$siteId?>'"
    />
</div>