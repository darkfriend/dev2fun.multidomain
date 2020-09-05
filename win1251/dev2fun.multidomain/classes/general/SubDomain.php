<?php
/**
 * @package subdomain
 * @author darkfriend
 * @version 0.2.0
 */

namespace Dev2fun\MultiDomain;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Localization\Loc;
use Dev2fun\MultiDomain\HLHelpers;

class SubDomain
{
    /**
     * value subdomain
     * @var string
     */
    private $subdomain;
    /**
     * @var object
     */
    private $csite;
    /**
     * Key for $GLOBALS
     * @var string
     */
    private $globalKey = 'subdomain'; //SUBDOMAIN
    private $globalLangKey = 'lang'; //SUBDOMAIN

    private $strictMode = true;

    private $cookieKey = 'subdomain';
    private $mainHost;
    private $httpHost = '';
    /**
     * Default value for default site
     * @var string
     */
    private $defaultVal = 'ru';
    /**
     * Lang list, exclude current lang
     * @var array
     */
    private $otherLangs;

    private $cacheEnable = true;

    private $domains = [];
    private $domainToLang = [];
    private $currentDomain = [];

    /** @var null|array all support domains */
    private $domainList = null;

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

    public function getHttpHost()
    {
        if (!$this->httpHost)
            $this->httpHost = preg_replace('#(\:\d+)#', '', $_SERVER['HTTP_HOST']);
        return $this->httpHost;
    }

    /**
     * check subdomain
     * @param bool $enable
     * @param array $params cacheTime|cacheID|cacheInit (CPHPCache::InitCache)
     * @return string|false subdomain
     */
    public function check($enable = true, $params = ['cacheTime' => 3600, 'cacheID' => null, 'cacheInit' => null])
    {
        global $APPLICATION;
        if (!$enable) return;
        $config = Config::getInstance();
        $moduleId = Base::$module_id;
        $subHost = '';

        $arNames = explode('.', $this->getHttpHost());
        $cntNames = count($arNames);
        switch ($cntNames) {
            case 2 :
                $host = $this->getHttpHost();
                break;
            default :
                $arNames = array_reverse($arNames);
                $host = $arNames[1] . '.' . $arNames[0];
                $subHost = [];
                if ($cntNames > 2) {
                    for ($i = 2; $i < $cntNames; $i++) {
                        if ($arNames[$i] == 'www') continue;
                        $subHost[] = $arNames[$i];
                    }
                    $subHost = array_reverse($subHost);
                    $subHost = implode('.', $subHost);
                }
                break;
        }
        if (!$subHost) $subHost = '';

        $hl = HLHelpers::getInstance();
        $hlDomain = $config->get('highload_domains');
        $this->domains = $hl->getElementList($hlDomain, [
            'UF_DOMAIN' => $host,
            'UF_SUBDOMAIN' => $subHost,
            //			'UF_ACTIVE' => 'Y',
        ]);
        if (!$this->domains) return;

        $this->mainHost = $host;
        //		$arDomainToLang = [];
        foreach ($this->domains as $key => $domain) {
            $subDomain = '';
            if ($domain['UF_SUBDOMAIN']) {
                $subDomain = $domain['UF_SUBDOMAIN'] . '.';
            }
            $this->domainToLang[$subDomain . $domain['UF_DOMAIN']] = $domain['UF_LANG'];
            $this->domains[$subDomain . $domain['UF_DOMAIN']] = $domain;
            unset($this->domains[$key]);
        }
        // if (!$this->isSupportHost($this->getHttpHost())) {
        // 	\CHTTP::SetStatus('404 Not Found');
        // }

        if ($config->get('logic_subdomain') != 'virtual') {
            if ($this->domainToLang[$this->getHttpHost()] == 'redirect') {
                $u = $this->redirectDomainProcess();
                if (!$u) return;
            }
        } elseif ($config->get('logic_subdomain') == 'virtual' && $this->domainToLang[$this->getHttpHost()] == 'redirect') {
            return $this->virtualDomainProcess();
        } else {
            $this->subdomain = $this->getSubDomain();
        }

        if (isset($this->domains[$this->getHttpHost()])) {
            $this->currentDomain = $this->domains[$this->getHttpHost()];
            $this->subdomain = $this->domainToLang[$this->getHttpHost()];
        }

        $GLOBALS[$this->getGlobalKey()] = $this->subdomain;
        $this->setCookie($this->subdomain);
        $this->checkLang();

        // if ($config->get('enable_multilang') == 'Y') {
        // 	if ($this->currentDomain['UF_LANG']) {
        // 		$lang = $this->currentDomain['UF_LANG'];
        // 	} else {
        // 		$lang = $config->get('lang_default');
        // 	}
        // 	$this->setLanguage($lang);
        // 	$GLOBALS[$this->globalLangKey] = $lang;
        // 	$this->setCookie($lang, $this->globalLangKey);
        // }

        return $this->currentDomain;
    }

    public function checkLang()
    {
        if (Config::getInstance()->get('enable_multilang') == 'Y') {
            if ($this->currentDomain['UF_LANG']) {
                $lang = $this->currentDomain['UF_LANG'];
            } else {
                $lang = Config::getInstance()->get('lang_default');
            }
            $this->setLanguage($lang);
            $GLOBALS[$this->globalLangKey] = $lang;
            $this->setCookie($lang, $this->globalLangKey);
        }
    }

    /**
     * Записывает куку
     * @param string $cookieKey
     * @return void
     */
    public function setCookie($subdomain, $cookieKey = null)
    {
        global $APPLICATION;
        if (!$cookieKey) $cookieKey = $this->cookieKey;
        $APPLICATION->set_cookie($cookieKey, $subdomain, time() + 3600 * 30 * 12, '/', '*.' . $this->mainHost);
    }

    /**
     * Возвращает куку
     * @param string $cookieKey
     * @return string
     */
    public function getCookie($cookieKey = null)
    {
        global $APPLICATION;
        if (!$cookieKey) $cookieKey = $this->cookieKey;
        return $APPLICATION->get_cookie($cookieKey);
    }

    /**
     * Возвращает домен первого уровня из ссылки
     * @param string $url
     * @return string
     */
    public function getParentHost($url)
    {
        $host = '';
        if (preg_match('#(\w+\.\w+)$#', $url, $match)) {
            $host = $match[1];
        }
        return $host;
    }

    public function getProtocol()
    {
        if (\CMain::IsHTTPS()) {
            return 'https';
        }
        return 'http';
    }

    public function setGlobal($key, $subdomain)
    {
        $GLOBALS[$key] = $subdomain;
    }

    /**
     * Процесс редиректа
     * @param bool $redirect
     * @return string
     */
    public function redirectDomainProcess($redirect = true)
    {
        global $APPLICATION;
        //		$config = Config::getInstance();
        $currentPage = $APPLICATION->GetCurUri();

        $subdomain = $this->searchSubdomain();
        $subDomainMaps = Config::getInstance()->get('mapping_list');
        if ($subDomainMaps) {
            $subDomainMaps = unserialize($subDomainMaps);
            foreach ($subDomainMaps as $subDomainMap) {
                if ($subDomainMap['KEY'] == $subdomain) {
                    $subdomain = $subDomainMap['SUBNAME'];
                    break;
                }
            }
        }

        $isSupport = false;
        $redirectHost = "$subdomain.{$this->mainHost}";
        $supportDomains = $this->getDomainList();
        if ($supportDomains) {
            foreach ($supportDomains as $sValue) {
                $host = '';
                if ($sValue['UF_SUBDOMAIN'])
                    $host .= "{$sValue['UF_SUBDOMAIN']}.";
                if ($sValue['UF_DOMAIN'])
                    $host .= $sValue['UF_DOMAIN'];
                if ($host == $redirectHost) {
                    $isSupport = true;
                    break;
                }
            }
        }

        if ($this->strictMode && !$isSupport) return;

        $this->setCookie($subdomain);
        $url = $this->getProtocol() . '://' . $redirectHost . $currentPage;
        if ($redirect) LocalRedirect($url);
        return $url;
    }

    public function virtualDomainProcess()
    {
        global $APPLICATION;
        //		$config = Config::getInstance();
        $currentPage = $APPLICATION->GetCurUri();

        $subdomain = $this->searchSubdomain();
        $subDomainMaps = Config::getInstance()->get('mapping_list');
        if ($subDomainMaps) {
            $subDomainMaps = unserialize($subDomainMaps);
            foreach ($subDomainMaps as $subDomainMap) {
                if ($subDomainMap['KEY'] == $subdomain) {
                    $subdomain = $subDomainMap['SUBNAME'];
                    break;
                }
            }
        }

        $isSupport = false;
        $redirectHost = "$subdomain.{$this->mainHost}";
        $supportDomains = $this->getDomainList();
        if ($supportDomains) {
            foreach ($supportDomains as $sValue) {
                $host = '';
                if ($sValue['UF_SUBDOMAIN'])
                    $host .= "{$sValue['UF_SUBDOMAIN']}.";
                if ($sValue['UF_DOMAIN'])
                    $host .= $sValue['UF_DOMAIN'];
                if ($host == $redirectHost) {
                    $isSupport = true;
                    $this->domains = [$host => $sValue];
                    $this->currentDomain = $sValue;
                    $this->subdomain = $sValue['UF_LANG'];
                    $this->setCookie($subdomain);
                    $this->checkLang();
                    break;
                }
            }
        }

        if ($this->strictMode && !$isSupport) return;

        return $isSupport;
    }

    public function getFullDomain($subDomain, $mainHost = null)
    {
        if (!$mainHost) $mainHost = $this->mainHost;
        if ($subDomain) $subDomain = $subDomain . '.';
        return $subDomain . $mainHost;
    }

    /**
     * Возвращает имя поддомена
     * @return string
     */
    public function getSubDomain()
    {
        //		$subdomain = $this->getCookie();
        //		if (!$subdomain) {
        $subdomain = $this->searchSubdomain();
        if (!$subdomain) return false;
        $fullDomain = $this->getFullDomain($subdomain);
        //		}
        //		var_dump($fullDomain, $this->domainToLang);
        //		$config = Config::getInstance();
        if (!in_array($fullDomain, $this->domainToLang)) {
            return false;
            //			$fullDomain = $config->get('domain_default');
        }
        return $subdomain;
    }

    /**
     * Ищет и возвращает поддомен.<br>
     * Если режим городом, то возвращает код города.<br>
     * Если режим стран, то возвращает код страны.
     * @return string
     */
    public function searchSubdomain()
    {
        global $APPLICATION;
        $cookie = $APPLICATION->get_cookie($this->cookieKey);
        $cookie = mb_strtolower(htmlspecialcharsbx($cookie));
        if ($cookie) {
            return $cookie;
        }
        $config = Config::getInstance();
        $keyIp = $config->get('key_ip');
        if (!$keyIp) $keyIp = 'HTTP_X_REAL_IP';
        if (empty($_SERVER[$keyIp])) return false;
        $record = (new Geo())->setIp($_SERVER[$keyIp]);
        if (!$record) {
            return '';
        }
        if ($config->get('type_subdomain') == 'city') {
            return $record->getCityCode();
        }
        return $record->getCountryCode();
    }

    public function getCurrent()
    {
        return $this->currentDomain;
    }

    /**
     * Check cache for host
     * @param array $params cacheTime|cacheID|cacheInit (CPHPCache::InitCache)
     * @return boolean
     */
    private function getCache($params)
    {
        if (!$params['cacheID']) $params['cacheID'] = md5($this->getHttpHost());
        if (!$params['cacheInit']) $params['cacheInit'] = '/dev2fun.multidomain/';
        $oCache = new \CPHPCache();
        if ($oCache->initCache($params['cacheTime'], $params['cacheID'], $params['cacheInit'])) {
            return $oCache->getVars()[0];
        }
        return false;
    }

    /**
     * Save data in cache
     * @param mixed $data save data
     * @param array $params ['cacheTime'=>'','cacheID'=>'','cacheInit'=>''] (CPHPCache::InitCache)
     */
    private function setCache($data, $params = [])
    {
        $oCache = new \CPHPCache();
        if (!$params['cacheID']) $params['cacheID'] = md5($this->getHttpHost());
        if (!$params['cacheInit']) $params['cacheInit'] = '/dev2fun.multidomain/';
        $oCache->StartDataCache($params['cacheTime'], $params['cacheID'], $params['cacheInit']);
        $oCache->EndDataCache((array)$data);
    }

    /**
     * regexp subdomain
     * @return string|false
     */
    public function match()
    {
        $host = $this->getHttpHost();
        $mainHost = $this->getMainHost();
        $host = str_replace($mainHost, '', $host);
        if (!$host) return $this->defaultVal;
        preg_match('#^(.*?)\.#', $host, $matches);
        if ($matches) return $matches[1];
        return false;
    }

    /**
     * Get main server host from bitrix setting
     * @return string
     */
    public function getMainHost()
    {
        if ($this->csite) {
            $host = $this->csite['SERVER_NAME'];
        } else {
            $rsSites = \CSite::GetList($by = "sort", $order = "desc", ['ACTIVE' => 'Y', 'DEF' => 'Y']);
            $host = '';
            if ($arSite = $rsSites->Fetch()) {
                $host = $arSite['SERVER_NAME'];
                $this->csite = $arSite;
            }
        }
        if (!$host) {
            $paramUrl = '';
            if ($this->csite) $paramUrl = '?LID=' . $this->csite['LID'] . '&tabControl_active_tab=edit1';
            \CAdminNotify::Add([
                'MESSAGE' => 'Необходимо указать "URL сервера" на странице <a href="/bitrix/admin/site_edit.php' . $paramUrl . '">настроек</a>',
            ]);
        }
        return $host;
    }

    /**
     * is exist current server host support
     * @param string $host server host
     * @param boolean $checkFile check support array from file
     * @return boolean
     */
    public function isSupportHost($host, $checkFile = false)
    {
        if (!$host) return false;
        $cacheParams = [
            'cacheID' => md5($host . '_check'),
            'cacheTime' => (3600 * 24 * 30),
        ];
        if ($this->cacheEnable && $checkStatus = $this->getCache($cacheParams)) {
            return $checkStatus;
        }
        $arSupportHost = [];
        if (!$this->csite) {
            $rsSites = \CSite::GetList($by = "sort", $order = "asc", ['ACTIVE' => 'Y', 'DEFAULT' => 'Y']);
            if (!$this->csite = $rsSites->Fetch()) {
                return false;
            }
        }
        $domains = $this->csite['DOMAINS'];
        if ($domains) {
            $arSupportHost = explode(PHP_EOL, $domains);
            foreach ($arSupportHost as &$sDomain) {
                $sDomain = trim($sDomain);
            }
        }

        $checkStatus = in_array($host, $arSupportHost);
        if ($this->cacheEnable && $checkStatus) {
            $this->setCache($checkStatus, $cacheParams);
        }
        return $checkStatus;
    }

    /**
     * get name subdomain
     * @return string|false
     */
    public function get()
    {
        return $this->subdomain;
    }

    /**
     * get name subdomain
     * @return string|false
     */
    public static function GetDomain()
    {
        return $GLOBALS[(new SubDomain())->getGlobalKey()];
    }

    /**
     * set name subdomain
     * @param string $subdomain
     */
    public function set($subdomain)
    {
        $GLOBALS[$this->getGlobalKey()] = $this->subdomain = $subdomain;
    }

    /**
     * Get key for $GLOBALS
     * @return string
     */
    public function getGlobalKey()
    {
        return $this->globalKey;
    }

    /**
     * Set language
     * @param string $lang
     */
    public function setLanguage($lang)
    {
        if ($lang) {
            Loc::setCurrentLang($lang);
            $application = \Bitrix\Main\Application::getInstance();
            $context = $application->getContext();
            $context->setLanguage($lang);
        }
    }

    /**
     * Get default lang
     * @return string
     */
    public function getDefaultLang()
    {
        return $this->defaultVal;
    }

    /**
     * Get default lang
     * @return string
     */
    public static function DefaultLang()
    {
        return (new SubDomain())->defaultVal;
    }

    /**
     * Get active lang list
     * @return array
     */
    public function getLangList()
    {
        return ['en', 'ru'];
    }

    public function getProperties($hlId)
    {
        $host = $this->getHttpHost();
        //$host = $_SERVER['HTTP_HOST'];
        $arHost = explode('.', $host);
        if (count($arHost) > 2) {
            $subHost = $arHost[0];
            $host = str_replace($subHost . '.', '', $host);
        } else {
            $subHost = '';
        }

        $domain = HLHelpers::getInstance()->getElementList($hlId, [
            'UF_SUBDOMAIN' => $subHost,
            'UF_DOMAIN' => $host,
        ]);
        if (empty($domain[0])) return false;
        return $domain[0];
    }

    public function getSubDomainByList($subDomain, $list = null)
    {
        if (!$subDomain) return false;
        if (!$list) $list = $this->getDomainList();
        foreach ($list as $item) {
            if ($item['UF_SUBDOMAIN'] == $subDomain) {
                return $item;
            }
        }
        return false;
    }

    public function getDomainList()
    {
        if (!$this->domainList) {
            $this->domainList = HLHelpers::getInstance()->getElementList(
                Config::getInstance()->get('highload_domains')
            );
        }
        return $this->domainList;
    }
}