<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 0.1.38
 */

namespace Dev2fun\MultiDomain;


class TemplateSeo extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
    /**
     * Обработчик события на вход получает имя требуемой функции
     * @param \Bitrix\Main\Event $event
     * @return string
     * @example {=get_city}
     */
    public static function EventHandler(\Bitrix\Main\Event $event)
    {
        $parameters = $event->getParameters();
        $functionName = $parameters[0];
        if ($functionName === "get_city") {
            $currentDomain = \Dev2fun\MultiDomain\Base::GetCurrentDomain();
            $cityName = $currentDomain['UF_NAME'];
            foreach (GetModuleEvents('dev2fun.multidomain', "OnBeforeSeoSetCityName", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, [&$cityName, $currentDomain]);
            return $cityName;
        } else {
            return '';
        }
    }
}