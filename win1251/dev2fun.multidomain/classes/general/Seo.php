<?php
/**
 * Class for SEO
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.2.0
 */

namespace Dev2fun\MultiDomain;


use Bitrix\Main\Config\Option;
use Dev2fun\MultiDomain\HLHelpers;

class Seo
{
    private static $instance;

    /**
     * Singleton instance.
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function show($hlId, $siteId = SITE_ID)
    {
        global $APPLICATION;
        if (!$hlId) return false;

        $seoData = $this->getDomain($hlId, false, false, $siteId);
        if (!$seoData) return false;

        $APPLICATION->SetPageProperty('title', $seoData['UF_TITLE']);

        // if(!empty($seoData['UF_H1'])) {
        // 	$APPLICATION->SetTitle($seoData['UF_H1']);
        // }
        if (!empty($seoData['UF_DESCRIPTION'])) {
            $APPLICATION->SetPageProperty('description', $seoData['UF_DESCRIPTION']);
        }
        if (!empty($seoData['UF_KEYWORDS'])) {
            $APPLICATION->SetPageProperty('keywords', $seoData['UF_KEYWORDS']);
        }
        return $seoData;
    }

    public function getDomain($hlId, $host = false, $path = false, $siteId = SITE_ID)
    {
        $curUrl = $this->getUrl();
        if (!$host) $host = $curUrl['host'];
        if (!$path) $path = $curUrl['path'];

        return $this->getQuery($hlId, $host, $path, $siteId);
    }

    public function setDomain($hlId, $arFields)
    {
        $curUrl = $this->getUrl();
        $id = $this->setQuery($hlId, array_merge([
            'UF_DOMAIN' => $curUrl['host'],
            'UF_PATH' => $curUrl['path'],
        ], $arFields));
        return $id;
    }

    public function getError()
    {
        $err = null;
        if(!empty(HLHelpers::$LAST_ERROR[0])) {
            $err = HLHelpers::$LAST_ERROR[0];
            if(is_object($err)) {
                /** @var \Bitrix\Main\ORM\EntityError $err */
                $err = $err->getMessage();
            }
        }

        return $err;
    }

    private function getQuery($hlId, $host, $path, $siteId = SITE_ID)
    {
        $hl = HLHelpers::getInstance();
        $el = $hl->getElementList($hlId, [
            'UF_SITE_ID' => $siteId,
            'UF_DOMAIN' => $host,
            'UF_PATH' => $path,
        ]);

        return (empty($el[0]) ? false : $el[0]);
    }

    private function setQuery($hlId, $arFields)
    {
        $hl = HLHelpers::getInstance();
        if (empty($arFields['ID'])) {
            $id = $hl->addElement($hlId, $arFields);
        } else {
            $id = $arFields['ID'];
            unset($arFields['ID']);
            $id = $hl->updateElement($hlId, $id, $arFields);
        }
        return (empty($id) ? false : $id);
    }

    private function getUrl()
    {
        $arUrl = parse_url($_SERVER['REQUEST_URI']);
        $result = [
            'host' => $_SERVER['HTTP_HOST'],
        ];
        if (isset($arUrl['path'])) {
            $result['path'] = $arUrl['path'];
        }
        return $result;
    }
}