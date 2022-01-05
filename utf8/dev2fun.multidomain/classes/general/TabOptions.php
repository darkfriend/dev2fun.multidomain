<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 * @since  0.2.0
 */

namespace Dev2fun\MultiDomain;


use Bitrix\Main\Loader;
use darkfriend\helpers\Singleton;

class TabOptions
{
    use Singleton;

    protected $typeMode = 'element';
    protected $typeBlock = '';
    protected $iblockId;
    protected $elementId = null;
    protected $iblockElementFields = [
        'NAME' => [
            'id' => '',
            'LABEL' => 'NAME',
            'CODE' => 'NAME',
            'FIELD' => 'INPUT',
            'VALUE' => '',
        ],
        'PREVIEW_TEXT' => [
            'id' => '',
            'LABEL' => 'PREVIEW_TEXT',
            'CODE' => 'PREVIEW_TEXT',
            'FIELD' => 'HTML',
            'VALUE' => '',
        ],
        'DETAIL_TEXT' => [
            'id' => '',
            'LABEL' => 'DETAIL_TEXT',
            'CODE' => 'DETAIL_TEXT',
            'FIELD' => 'HTML',
            'VALUE' => '',
        ],
    ];
    protected $iblockElementProperties = null;
    protected $iblockSectionFields = [
        'NAME' => [
            'id' => '',
            'LABEL' => 'NAME',
            'CODE' => 'NAME',
            'FIELD' => 'INPUT',
            'VALUE' => '',
        ],
        'DESCRIPTION' => [
            'id' => '',
            'LABEL' => 'DESCRIPTION',
            'CODE' => 'DESCRIPTION',
            'FIELD' => 'HTML',
            'VALUE' => '',
        ],
        'DETAIL_TEXT' => [
            'id' => '',
            'LABEL' => 'DETAIL_TEXT',
            'CODE' => 'DETAIL_TEXT',
            'FIELD' => 'HTML',
            'VALUE' => '',
        ],
    ];
    protected $iblockSectionProperties = null;
    protected $elements = [];

    protected function __construct($options = [])
    {
        if(isset($_REQUEST['ENTITY_ID'])) {
            $this->iblockId = $_REQUEST['ENTITY_ID'];
            $this->typeBlock = 'hl';
        } else {
            $this->iblockId = $_REQUEST['IBLOCK_ID'];
        }
        if(!empty($_REQUEST['ID'])) {
            $this->elementId = $_REQUEST['ID'];
        }
        $this->typeMode = Base::getTypePage();
    }

    public function getDomainName($domainElement)
    {
        if($domainElement['UF_SUBDOMAIN']==='main') {
            return $domainElement['UF_DOMAIN'];
        }
        $logic = Config::getInstance()->get('logic_subdomain', SubDomain::LOGIC_SUBDOMAIN, Site::getCurrent());
        if($logic === SubDomain::LOGIC_DIRECTORY) {
            return "{$domainElement['UF_DOMAIN']}/{$domainElement['UF_SUBDOMAIN']}";
        }
        return "{$domainElement['UF_SUBDOMAIN']}.{$domainElement['UF_DOMAIN']}";
    }

    public function getFieldPropertyType($property)
    {
        $type = 'INPUT';
        if(!empty($property['USER_TYPE'])) {
            switch ($property['USER_TYPE']) {
                case 'HTML':
                    $type = 'HTML';
                    break;
                default:
                    if($property['ROW_COUNT']>1) {
                        $type = 'TEXTAREA';
                    }
            }
        }
        return $type;
    }

    public function getFieldInfo($field)
    {
        if($field['UF_FIELD_TYPE']==='section') {
            if(!isset($this->iblockSectionFields[$field['UF_FIELD']])) {
                return false;
            }
            $field['FORM'] = $this->iblockSectionFields[$field['UF_FIELD']];
        } else {
            if(\is_numeric($field['UF_FIELD'])) {
                $field['PROPERTY'] = $this->getIblockProperty($field['UF_FIELD']);
                if(!$field['PROPERTY']) {
                    return false;
                }
                $field['FORM'] = [
//                    'ID' => $field['PROPERTY']['ID'],
                    'LABEL' => $field['PROPERTY']['NAME'].' (PROPERTY)',
                    'CODE' => $field['PROPERTY']['CODE'],
                    'FIELD' => $this->getFieldPropertyType($field['PROPERTY']),
                    'MULTIPLE' => $field['PROPERTY']['MULTIPLE']==='Y',
                    'VALUE' => '',
                ];
            } else {
                if(!isset($this->iblockElementFields[$field['UF_FIELD']])) {
                    return false;
                }
                $field['FORM'] = $this->iblockElementFields[$field['UF_FIELD']];
            }
        }

        return $field;
    }

    public function showElements($domainElement, $inxd, $siteId = null)
    {
        if(!$domainElement) return false;
        if(!$siteId) {
            $siteId = Site::getCurrent();
        }
        $hl = \Darkfriend\HLHelpers::getInstance();
        $fields = $hl->getElementList(
            Config::getInstance()->getCommon('lang_fields'),
            [
                'UF_SITE_ID' => $siteId,
                'UF_IBLOCK_ID' => $this->typeBlock.$this->iblockId,
                'UF_FIELD_TYPE' => $this->typeMode,
            ]
        );
        if(!$fields) return false;

        foreach ($fields as $k=>$field) {
            $field = $this->getFieldInfo($field);
            if(!$field) continue;
            if(isset($this->getElements($siteId)[$domainElement['ID']][$field['ID']])) {
                $elementValue = $this->getElements($siteId)[$domainElement['ID']][$field['ID']];
                if(!empty($elementValue['UF_VALUE_STRING'])) {
                    $field['FORM']['VALUE'] = $elementValue['UF_VALUE_STRING'];
                } elseif(!empty($elementValue['UF_VALUE_TEXT'])) {
                    $field['FORM']['VALUE'] = $elementValue['UF_VALUE_TEXT'];
                }
            }

            if(empty($field['FORM']['NAME'])) {
                $field['FORM']['NAME'] = $field['FORM']['CODE'];
            }
            if(empty($field['FORM']['LABEL'])) {
                $field['FORM']['LABEL'] = $field['FORM']['CODE'];
            }
            $field['FORM']['ID'] = $field['ID'];
            $field['FORM']['NAME'] = "Dev2funLang[{$inxd}][FIELDS][{$k}][VALUE]";
//            $field['FORM']['TYPE_NAME'] = "Dev2funLang[{$inxd}][FIELDS][{$k}][TYPE]";
            $field['FORM']['TYPE_INPUT'] = "Dev2funLang[{$inxd}][FIELDS][{$k}][TYPE]";
            $field['FORM']['ID_INPUT'] = "Dev2funLang[{$inxd}][FIELDS][{$k}][ID]";
            echo "
            <tr id=\"tr_dev2fun_{$field['FORM']['CODE']}\">
                <td class=\"adm-detail-content-cell-l\">
                    <span class=\"adm-required-field\">{$field['FORM']['LABEL']}:</span>
                </td>
                <td style=\"white-space: nowrap;\" class=\"adm-detail-content-cell-r\">
                    <input type=\"hidden\" name=\"{$field['FORM']['ID_INPUT']}\" value=\"{$field['FORM']['ID']}\">
                ";
            $this->output($field);
            echo    "</td>
            </tr>";
        }

        return true;
    }

    /**
     * Возвращает инфо о свойстве инфоблока
     * @param integer $propertyId
     * @return array
     */
    public function getIblockProperty($propertyId)
    {
        if(!$propertyId) return [];
        if(!isset($this->iblockElementProperties)) {
            $this->iblockElementProperties = [];
            $rsIblocks = \CIBlock::GetProperties(
                $this->iblockId,
                ['NAME'=>'ASC']
            );
            while ($prop = $rsIblocks->GetNext()) {
                if(!\in_array($prop['PROPERTY_TYPE'],['S','N'])) {
                    continue;
                }
                $this->iblockElementProperties[$prop['ID']] = $prop;
            }
        }

        if(!isset($this->iblockElementProperties[$propertyId])) {
            return [];
        }

        return $this->iblockElementProperties[$propertyId];
    }

    public function output($field)
    {
        switch ($field['FORM']['FIELD']) {
            case 'INPUT':
                echo $this->getInput($field);
                break;
            case 'TEXTAREA':
                echo $this->getTextarea($field);
                break;
            case 'HTML':
                $this->getHtml($field);
                break;
        }
    }

    public function getInput($field)
    {
        return "<input type=\"text\" name=\"{$field['FORM']['NAME']}\" id=\"{$field['FORM']['CODE']}\" maxlength=\"255\" value=\"{$field['FORM']['VALUE']}\">"
            ."<input type=\"hidden\" name=\"{$field['FORM']['TYPE_INPUT']}\" value=\"INPUT\">";
    }

    public function getTextarea($field)
    {
        if(empty($field['COL_COUNT'])) {
            $field['COL_COUNT'] = 30;
        }
        if(empty($field['ROW_COUNT'])) {
            $field['ROW_COUNT'] = 10;
        }
        return "<textarea name=\"{$field['FORM']['NAME']}\" cols=\"{$field['FORM']['COL_COUNT']}\" rows=\"{$field['FORM']['ROW_COUNT']}\"></textarea>"
            ."<input type=\"hidden\" name=\"{$field['FORM']['TYPE_INPUT']}\" value=\"TEXTAREA\">";
    }

    public function getHtml($field)
    {
        Loader::includeModule('fileman');
        $LHE = new \CHTMLEditor();
        $LHE->Show(array(
            'name' => $field['FORM']['NAME'],
            'id' => $field['FORM']['NAME'],
            'inputName' => $field['FORM']['NAME'],
            'content' => $field['FORM']['VALUE'],
            'width' => '100%',
            'minBodyWidth' => 350,
            'normalBodyWidth' => 555,
            'height' => '200',
            'bAllowPhp' => false,
            'limitPhpAccess' => false,
            'autoResize' => true,
            'autoResizeOffset' => 40,
            'useFileDialogs' => false,
            'saveOnBlur' => true,
            'showTaskbars' => false,
            'showNodeNavi' => false,
            'askBeforeUnloadPage' => true,
            'bbCode' => false,
            'siteId' => \SITE_ID,
            'controlsMap' => array(
                array('id' => 'Bold', 'compact' => true, 'sort' => 80),
                array('id' => 'Italic', 'compact' => true, 'sort' => 90),
                array('id' => 'Underline', 'compact' => true, 'sort' => 100),
                array('id' => 'Strikeout', 'compact' => true, 'sort' => 110),
                array('id' => 'RemoveFormat', 'compact' => true, 'sort' => 120),
                array('id' => 'Color', 'compact' => true, 'sort' => 130),
                array('id' => 'FontSelector', 'compact' => false, 'sort' => 135),
                array('id' => 'FontSize', 'compact' => false, 'sort' => 140),
                array('separator' => true, 'compact' => false, 'sort' => 145),
                array('id' => 'OrderedList', 'compact' => true, 'sort' => 150),
                array('id' => 'UnorderedList', 'compact' => true, 'sort' => 160),
                array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
                array('separator' => true, 'compact' => false, 'sort' => 200),
                array('id' => 'InsertLink', 'compact' => true, 'sort' => 210),
                array('id' => 'InsertImage', 'compact' => false, 'sort' => 220),
                array('id' => 'InsertVideo', 'compact' => true, 'sort' => 230),
                array('id' => 'InsertTable', 'compact' => false, 'sort' => 250),
                array('separator' => true, 'compact' => false, 'sort' => 290),
                array('id' => 'Fullscreen', 'compact' => false, 'sort' => 310),
                array('id' => 'More', 'compact' => true, 'sort' => 400)
            ),
        ));
        echo "<input type=\"hidden\" name=\"{$field['FORM']['TYPE_INPUT']}\" value=\"HTML\">";
    }

    public function getElements($siteId = null)
    {
        if(!$this->elementId) return [];
        if(!$siteId) {
            $siteId = Site::getCurrent();
        }
        if(!isset($this->elements[$this->elementId])) {
            $items = HLHelpers::getInstance()->getElementList(
                Config::getInstance()->getCommon('lang_data'),
                [
                    'UF_SITE_ID' => $siteId,
                    'UF_ELEMENT_ID' => $this->elementId,
                    'UF_REF_TYPE' => $this->typeMode,
                ]
            );
            $this->elements[$this->elementId] = [];
            foreach ($items as $item) {
                $this->elements[$this->elementId][$item['UF_DOMAIN_ID']][$item['UF_FIELD_ID']] = $item;
            }
        }
        return $this->elements[$this->elementId];
    }
}