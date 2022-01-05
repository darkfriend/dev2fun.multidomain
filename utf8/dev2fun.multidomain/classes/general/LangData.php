<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 * @since 0.2.1
 */

namespace Dev2fun\MultiDomain;


class LangData
{
    public static function saveElement($arFields)
    {
        $arFields['REF_TYPE'] = 'element';
        self::save($arFields);
    }

    public static function saveSection($arFields)
    {
        $arFields['REF_TYPE'] = 'section';
        self::save($arFields);
    }

    /**
     * @param array $arFields
     * @param null|string $siteId
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public static function save($arFields, $siteId = null)
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        if(empty($arFields['ID'])) return;
        if(empty($arFields['REF_TYPE'])) {
            $arFields['REF_TYPE'] = 'element';
        }
        if(!$siteId) {
            $siteId = Site::getCurrent();
        }

        $elements = $_REQUEST['Dev2funLang'];
        if(!$elements) return;
        $hl = \Darkfriend\HLHelpers::getInstance();
        $elementList = $hl->getElementList(
            Config::getInstance()->getCommon('lang_data'),
            [
                'UF_SITE_ID' => $siteId,
                'UF_ELEMENT_ID' => $arFields['ID'],
                'UF_REF_TYPE' => $arFields['REF_TYPE'],
            ]
        );
        if($elementList) {
            foreach ($elementList as $k => $item) {
                $elementList[$item['UF_DOMAIN_ID']][$item['UF_FIELD_ID']] = $item;
                unset($elementList[$k]);
            }
        }

        /**
         * @var array $element = [
         *      'DOMAIN_ID' => '',
         *      'FIELDS' => [],
         * ]
         */
        foreach ($elements as $element) {
            if(empty($element['FIELDS'])) continue;
            /**
             * @var array $field = [
             *      'ID' => '',
             *      'VALUE' => '',
             *      'TYPE' => '',
             * ]
             */
            foreach ($element['FIELDS'] as $field) {
                $addFields = [
                    'UF_SITE_ID' => $siteId,
                    'UF_DOMAIN_ID' => $element['DOMAIN_ID'],
                    'UF_FIELD_ID' => $field['ID'],
                    'UF_ELEMENT_ID' => $arFields['ID'],
                    'UF_REF_TYPE' => $arFields['REF_TYPE'],
                    'UF_VALUE_TYPE' => $field['TYPE'],
                ];
                switch ($field['TYPE']) {
                    case 'TEXTAREA':
                    case 'HTML':
                        $addFields['UF_VALUE_TEXT'] = $field['VALUE'];
                        break;
                    case 'INPUT':
                    default:
                        $addFields['UF_VALUE_STRING'] = $field['VALUE'];
                        break;
                }
                if(!empty($elementList[$addFields['UF_DOMAIN_ID']][$addFields['UF_FIELD_ID']])) {
                    $hl->updateElement(
                        Config::getInstance()->getCommon('lang_data'),
                        $elementList[$addFields['UF_DOMAIN_ID']][$addFields['UF_FIELD_ID']]['ID'],
                        $addFields
                    );
                } else {
                    $hl->addElement(Config::getInstance()->getCommon('lang_data'), $addFields);
                }
            }
        }
    }

    /**
     * @param array $arFields
     * @param null|string $siteId
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     */
    public static function deleteElement($arFields, $siteId = null)
    {
        if(empty($arFields['REF_TYPE'])) {
            $arFields['REF_TYPE'] = 'element';
        }
        if(!$siteId) {
            $siteId = Site::getCurrent();
        }
        $hl = \Darkfriend\HLHelpers::getInstance();
        $elementList = $hl->getElementList(
            Config::getInstance()->getCommon('lang_data'),
            [
                'UF_SITE_ID' => $siteId,
                'UF_ELEMENT_ID' => $arFields['ID'],
                'UF_REF_TYPE' => $arFields['REF_TYPE'],
            ]
        );
        if($elementList) {
            foreach ($elementList as $item) {
                $hl->deleteElement(Config::getInstance()->get('lang_data'), $item['ID']);
            }
        }
    }

    /**
     * @param array|int $fields
     * @param string $refType element|section
     * @param array $params EXCLUDE_FIELDS
     * @param string|null $siteId
     * @return array|bool
     */
    public static function getDataFields($fields, $refType='element', $params=[], $siteId = null)
    {
        $arDomain = Base::GetCurrentDomain();
        if(empty($arDomain)) return $fields;
        if(!$siteId) {
            $siteId = Site::getCurrent();
        }

        if(!\is_array($fields)) {
            if($refType==='section') {
                $fields = \CIBlockSection::GetByID($fields)->Fetch();
            } else {
                $rsBlock = \CIBlockElement::GetByID($fields)->GetNextElement();
                $fields = $rsBlock->GetFields();
                $fields['PROPERTIES'] = $rsBlock->GetProperties();
            }
        }

        $hl = \Darkfriend\HLHelpers::getInstance();
        $elementList = $hl->getElementList(
            Config::getInstance()->getCommon('lang_data'),
            [
                'UF_SITE_ID' => $siteId,
                'UF_DOMAIN_ID' => $arDomain['ID'],
                'UF_ELEMENT_ID' => $fields['ID'],
                'UF_REF_TYPE' => $refType,
            ]
        );
        if(!$elementList) return $fields;
        $elementListLoc = [];
        foreach ($elementList as $item) {
            $elementListLoc[$item['UF_FIELD_ID']] = $item;
        }
        $elementList = $elementListLoc;
        unset($elementListLoc);

        $fieldsList = $hl->getElementList(
            Config::getInstance()->getCommon('lang_fields'),
            [
                'ID' => \array_keys($elementList),
            ]
        );
        if(!$fieldsList) return $fields;
        $fieldsListLoc = [];
        foreach ($fieldsList as $item) {
            $fieldsListLoc[$item['UF_FIELD']] = $item;
        }
        $fieldsList = $fieldsListLoc;
        unset($fieldsListLoc);

        foreach ($fieldsList as $code => $item) {
            if(!empty($params['EXCLUDE_FIELDS']) && \in_array($code, $params['EXCLUDE_FIELDS'])) {
                continue;
            }
            if(empty($elementList[$item['ID']])) continue;
            $data = $elementList[$item['ID']];
            if(empty($data['UF_VALUE_TEXT']) && empty($data['UF_VALUE_STRING'])) {
                continue;
            }
            if(!\is_numeric($code) && isset($fields[$code])) {
                if($data['UF_VALUE_TYPE']==='HTML') {
                    $fields[$code] = $data['UF_VALUE_TEXT'];
                } else {
                    $fields[$code] = $data['UF_VALUE_STRING'];
                }
            } elseif($refType==='element' && !empty($fields['PROPERTIES'])) {
                $props = \array_filter($fields['PROPERTIES'],function($propItem) use ($code){
                    return $propItem['ID']==$code;
                });
                if(empty($props)) continue;
                foreach ($props as $kProp => $prop) {
                    if($data['UF_VALUE_TYPE']==='HTML') {
                        $fields['PROPERTIES'][$kProp]['VALUE'] = $data['UF_VALUE_TEXT'];
                    } else {
                        $fields['PROPERTIES'][$kProp]['VALUE'] = $data['UF_VALUE_STRING'];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * @param array $items
     * @param string $type
     * @param array $params
     * @param null|string $siteId
     * @return array
     */
    public static function getLangItemsFields($items, $type='element', $params = [], $siteId = null)
    {
        if($items) {
            foreach ($items as &$item) {
                $item = self::getDataFields($item, $type, $params, $siteId);
            }
            unset($item);
        }

        return $items;
    }

    /**
     * Load messages from template dir
     * @param string $file path to file or filename
     */
    public static function loadMessages($file)
    {
        if(is_file($file)) {
            $file = pathinfo($file, PATHINFO_FILENAME);
        }
        \Bitrix\Main\Localization\Loc::loadMessages(
            $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH."/{$file}.php"
        );
    }

    /**
     * @param string $code
     * @param null|string $replace
     * @param null|string $language
     * @return string
     */
    public static function getMessage($code, $replace = null, $language = null)
    {
        $str = \Bitrix\Main\Localization\Loc::getMessage($code, $replace, $language);
        return $str ?? $code;
    }
}