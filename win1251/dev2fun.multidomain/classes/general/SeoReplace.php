<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;


class SeoReplace
{
    const KEY_TITLE = 'Title';
    const KEY_DESCRIPTION = 'Description';
    const KEY_KEYWORDS = 'Keywords';
    const KEY_HEADING = 'Heading';

    /**
     * @param string $type
     * @param array $parameters
     * @return string
     */
    public static function getCalculateSeo($type, $parameters)
    {
        if(empty($parameters[0])) {
            $field = 'UF_NAME';
        } else {
            $field = ToUpper($parameters[0]);
            if(strpos($field,'UF_') === false) {
                $field = "UF_{$field}";
            }
        }

        $result = [
            'field' => $field,
        ];

        if(!empty($parameters[1])) {
            $result['beforeText'] = $parameters[1];
        }

        if(!empty($parameters[2])) {
            $result['afterText'] = $parameters[2];
        }

        if(!empty($parameters[3])) {
            $result['additional'] = explode(',', $parameters[3]);
        }

//        foreach (GetModuleEvents('dev2fun.multidomain', "OnBeforeSeoSetCityName", true) as $arEvent) {
//            ExecuteModuleEventEx($arEvent, [&$cityName, $currentDomain]);
//        }
        return "{{$type}:".json_encode($result).'}';
    }

    /**
     * @param string $content
     */
    public static function process(&$content)
    {
        self::replaceTitle($content);
        self::replaceDescription($content);
        self::replaceKeywords($content);
        self::replaceHeading($content);
    }

    /**
     * @param string $content
     */
    public static function replaceTitle(&$content)
    {
        global $APPLICATION;
        $prop = $APPLICATION->GetPageProperty('title');
        $key = SeoReplace::KEY_TITLE;
        if(preg_match("#\{{$key}\:\{(.*?)\}\}#i", $prop, $matches)) {
            $str = html_entity_decode($matches[1]);
            $titleJson = json_decode("{{$str}}", true);
            $replaceTitle = Base::GetCurrentDomain()[$titleJson['field']] ?? '';
            if($replaceTitle) {
                if(!empty($titleJson['beforeText'])) {
                    $replaceTitle = "{$titleJson['beforeText']} $replaceTitle";
                }
                if(!empty($titleJson['afterText'])) {
                    $replaceTitle .= $titleJson['afterText'];
                }
            }
            $replaceTitleFull = str_replace(
                $matches[0],
                $replaceTitle,
                $prop
            );
            $APPLICATION->SetPageProperty('title', $replaceTitleFull);
            $APPLICATION->SetTitle($replaceTitleFull);
            $content = str_replace(
                $matches[0],
                $replaceTitle,
                $content
            );
            return true;
        }



        return false;
    }

    /**
     * @param string $content
     */
    public static function replaceDescription(&$content)
    {
        global $APPLICATION;
        $prop = $APPLICATION->GetPageProperty('description');
        $key = SeoReplace::KEY_DESCRIPTION;
        if(preg_match("#\{{$key}\:\{(.*?)\}\}#i", $prop, $matches)) {
            $propDecoded = html_entity_decode($matches[1]);
            $titleJson = json_decode("{{$propDecoded}}", true);
            $replaceProp = Base::GetCurrentDomain()[$titleJson['field']] ?? '';
            if($replaceProp) {
                if(!empty($titleJson['beforeText'])) {
                    $replaceProp = "{$titleJson['beforeText']} $replaceProp";
                }
                if(!empty($titleJson['afterText'])) {
                    $replaceProp .= $titleJson['afterText'];
                }
            }
            $replacePropFull = str_replace(
                $matches[0],
                $replaceProp,
                $prop
            );
            $APPLICATION->SetPageProperty('description', $replacePropFull);
            $content = str_replace(
                htmlentities($matches[0]),
                $replaceProp,
                $content
            );
        }
    }

    /**
     * @param string $content
     */
    public static function replaceKeywords(&$content)
    {
        global $APPLICATION;
        $prop = $APPLICATION->GetPageProperty('keywords');
        $key = SeoReplace::KEY_KEYWORDS;
        $regExp = "#\{{$key}\:\{(.*?)\}\}#i";
        if(preg_match($regExp, $prop, $matches)) {
            $propDecoded = html_entity_decode($matches[1]);
            $titleJson = json_decode("{{$propDecoded}}", true);
            $replaceProp = Base::GetCurrentDomain()[$titleJson['field']] ?? '';
            if($replaceProp) {
                if(!empty($titleJson['beforeText'])) {
                    $replaceProp = "{$titleJson['beforeText']} $replaceProp";
                }
                if(!empty($titleJson['afterText'])) {
                    $replaceProp .= $titleJson['afterText'];
                }
            }
            $searchString = "{{$key}:{{$matches[1]}}}";
            $replacePropFull = str_replace(
                $searchString,
                $replaceProp,
                $prop
            );
            $APPLICATION->SetPageProperty('keywords', $replacePropFull);
            $content = str_replace(
                htmlentities($searchString),
                $replaceProp,
                $content
            );
            self::replaceKeywords($content);
        }
    }

    /**
     * @param string $content
     */
    public static function replaceHeading(&$content)
    {
        $key = SeoReplace::KEY_HEADING;
        if(preg_match("#\{{$key}\:\{(.*?)\}\}#i", $content, $matches)) {
            $propDecoded = html_entity_decode($matches[1]);
            $titleJson = json_decode("{{$propDecoded}}", true);
            $replaceProp = Base::GetCurrentDomain()[$titleJson['field']] ?? '';
            if($replaceProp) {
                if(!empty($titleJson['beforeText'])) {
                    $replaceProp = "{$titleJson['beforeText']} $replaceProp";
                }
                if(!empty($titleJson['afterText'])) {
                    $replaceProp .= $titleJson['afterText'];
                }
            }
            $content = str_replace(
                $matches[0],
                $replaceProp,
                $content
            );
        }
    }
}