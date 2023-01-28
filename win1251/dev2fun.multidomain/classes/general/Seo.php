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

    /**
     * @param int $hlId
     * @param string|null $siteId
     * @return false|mixed
     */
    public function show($hlId, $siteId = null)
    {
        global $APPLICATION;

        if (!$hlId) {
            return false;
        }

        $seoData = $this->getDomain($hlId, false, false, $siteId);
        if (!$seoData) {
            return false;
        }

        if (!empty($seoData['UF_TITLE'])) {
            $APPLICATION->SetPageProperty('title', $seoData['UF_TITLE']);
        }

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

    /**
     * @param int $hlId
     * @param string|null $host
     * @param string|null $path
     * @param string|null $siteId
     * @return false|mixed
     */
    public function getDomain($hlId, $host = null, $path = null, $siteId = null)
    {
        $curUrl = $this->getUrl();
        if (!$host) $host = $curUrl['host'];
        if (!$path) $path = $curUrl['path'];

        return $this->getQuery($hlId, $host, $path, $siteId);
    }

    /**
     * @param int $hlId
     * @param array $arFields
     * @return bool|int
     */
    public function setDomain($hlId, $arFields)
    {
        $curUrl = $this->getUrl();
        $id = $this->setQuery($hlId, array_merge([
            'UF_DOMAIN' => $curUrl['host'],
            'UF_PATH' => $curUrl['path'],
        ], $arFields));
        return $id;
    }

    /**
     * @return \Bitrix\Main\ORM\EntityError|null
     */
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

    /**
     * @param int $hlId
     * @param string|null $host
     * @param string|null $path
     * @param string|null $siteId
     * @return false|mixed
     */
    private function getQuery(int $hlId, ?string $host, ?string $path, ?string $siteId)
    {
        $hl = HLHelpers::getInstance();
        $el = $hl->getElementList($hlId, [
            'UF_SITE_ID' => $siteId,
            'UF_DOMAIN' => $host,
            'UF_PATH' => $path,
        ]);

        return (empty($el[0]) ? false : $el[0]);
    }

    /**
     * @param int $hlId
     * @param array $arFields
     * @return bool|int
     */
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

    /**
     * @return array
     */
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