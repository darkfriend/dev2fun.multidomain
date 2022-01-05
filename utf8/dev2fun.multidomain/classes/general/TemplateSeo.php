<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 */

namespace Dev2fun\MultiDomain;


use Bitrix\Main\EventResult;

class TemplateSeo
{
    /**
     * Обработчик события на вход получает имя требуемой функции
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\EventResult|void
     * @example {=multiTitle} or {=multiDescription} or {=multiKeywords} or {=multiHeading} or {=multiLangField}
     */
    public static function EventHandler(\Bitrix\Main\Event $event)
    {
        $parameters = $event->getParameters();
        $functionName = $parameters[0];

        $class = null;
        switch ($functionName) {
            case 'multiTitle':
            case 'multititle':
                $class = 'Dev2fun\MultiDomain\TemplateSeoTitleCalculate';
                break;
            case 'multiLangField':
            case 'multilangfield':
                $class = 'Dev2fun\MultiDomain\TemplateLangFieldCalculate';
                break;
            case 'multiDescription':
            case 'multidescription':
                $class = 'Dev2fun\MultiDomain\TemplateSeoDescriptionCalculate';
                break;
            case 'multiKeywords':
            case 'multikeywords':
                $class = 'Dev2fun\MultiDomain\TemplateSeoKeywordsCalculate';
                break;
            case 'multiHeading':
            case 'multiheading':
                $class = 'Dev2fun\MultiDomain\TemplateSeoHeadingCalculate';
                break;
        }

        if ($class) {
            return new \Bitrix\Main\EventResult(
                EventResult::SUCCESS,
                $class,
                'dev2fun.multidomain'
            );
        }
    }
}
