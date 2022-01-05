<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;


class TemplateLangFieldCalculate extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
    const TYPE_ELEMENT = 'element';
    const TYPE_IBLOCK = 'iblock';
    const TYPE_SECTION = 'section';
    const TYPE_ELEMENT_SECTION = 'elementCatalog';

    public function onPrepareParameters(\Bitrix\Iblock\Template\Entity\Base $entity, array $parameters = [])
    {
        if($entity instanceof \Bitrix\Iblock\Template\Entity\Element) {
            $type = self::TYPE_ELEMENT;
        } elseif ($entity instanceof \Bitrix\Iblock\Template\Entity\Iblock) {
            $type = self::TYPE_IBLOCK;
        } elseif ($entity instanceof \Bitrix\Iblock\Template\Entity\Section) {
            $type = self::TYPE_SECTION;
        } elseif ($entity instanceof \Bitrix\Iblock\Template\Entity\ElementCatalog) {
            $type = self::TYPE_ELEMENT;
        }
        $arguments = [
            'type' => $type,
            'id' => $entity->getId(),
        ];
        /** @var \Bitrix\Iblock\Template\NodeBase $parameter */
        foreach ($parameters as $parameter) {
            $arguments[] = $parameter->process($entity);
        }
        return $arguments;
    }

    /**
     * Handler
     * @param array $parameters Function parameters.
     * @return string
     */
    public function calculate(array $parameters = [])
    {
        $item = $this->getItem($parameters['id'], $parameters['type']);
        if(!$item) {
            return '';
        }

        $item = LangData::getDataFields($item, $parameters['type']);

        if(empty($parameters[0])) {
            $field = 'NAME';
        } else {
            $field = ToUpper($parameters[0]);
        }

        if(empty($item[$field])) {
            return '';
        }

        $result = $item[$field];

        if(!empty($parameters[1])) {
            $result = "{$parameters[1]} {$result}";
        }

        if(!empty($parameters[2])) {
            $result .= " {$parameters[2]}";
        }

        return $result;
    }

    /**
     * @param int $id
     * @param string $type
     * @return array
     */
    protected function getItem($id, $type)
    {
        switch ($type) {
            case self::TYPE_ELEMENT_SECTION:
            case self::TYPE_ELEMENT:
                $rs = \CIBlockElement::GetByID($id);
                if(!$rs) break;
                return $rs->GetNext();
            case self::TYPE_IBLOCK:
                $rs = \CIBlock::GetByID($id);
                if(!$rs) break;
                return $rs->GetNext();
            case self::TYPE_SECTION:
                $rs = \CIBlockSection::GetByID($id);
                if(!$rs) break;
                return $rs->GetNext();
        }

        return [];
    }
}
