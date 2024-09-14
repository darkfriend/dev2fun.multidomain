<?php
/**
 * @package subdomain
 * @author darkfriend
 * @version 1.2.1
 */

namespace Dev2fun\MultiDomain;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Uploader\Package;
use darkfriend\helpers\ArrayHelper;

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

    /**
     * Subdomain list
     * @var DomainEntity[]|array
     */
    private $domains = [];
    /**
     * Subdomain with langs
     * @var DomainEntity[]|array
     */
    private $domainToLang = [];
    /**
     * Current subdomain
     * @var DomainEntity|array
     */
    private $currentDomain = [];

    /** @var DomainEntity[]|null|array all support domains */
    private $domainList = null;

    private static $instance;

    /** @var string */
    const TYPE_CITY = 'city';
    /** @var string */
    const TYPE_COUNTRY = 'country';
    /** @var string */
    const TYPE_LANG = 'lang';
    /** @var string */
    const TYPE_VIRTUAL = 'virtual';

    /** @var string */
    const LOGIC_SUBDOMAIN = 'subdomain';
    /** @var string */
    const LOGIC_DIRECTORY = 'directory';
    /**
     * @deprecated
     */
    const LOGIC_VIRTUAL = 'virtual';
    /**
     * default name of subdomain
     */
    const DEFAULT_SUBDOMAIN = 'main';

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
     * @return string|null
     */
    public function getHttpHost()
    {
        if (!$this->httpHost) {
            if(empty($_SERVER['HTTP_HOST'])) {
                $host = '';
            } else {
                $host = $_SERVER['HTTP_HOST'];
            }
            $this->httpHost = \preg_replace('#(\:\d+)#', '', $host);
        }
        return $this->httpHost;
    }

    /**
     * Event onAfterFindCurrentSubdomain
     * @return void
     */
    public function onAfterFindCurrentSubdomain()
    {
        $event = new Event(Base::$module_id, 'onAfterFindCurrentSubdomain');
        $event->send();
    }

    /**
     * Event onBeforeSetNotFound
     * @return bool
     */
    public function onBeforeSetNotFound()
    {
        $isSetNotFound = true;
        $event = new Event(Base::$module_id, 'onBeforeSetNotFound', [
            'isSetNotFound' => $isSetNotFound,
        ]);
        $event->send();
        foreach ($event->getResults() as $eventResult) {
            if ($eventResult->getType() === EventResult::SUCCESS) {
                ['isSetNotFound' => $isSetNotFound] = $eventResult->getModified();
            }
        }
        return $isSetNotFound ?? true;
    }

    /**
     * Check subdomain
     * @param bool $enable
     * @param string $siteId
     * @param array $params cacheTime|cacheID|cacheInit (CPHPCache::InitCache)
     * @return string|false subdomain
     */
    public function check(
        $enable = true,
        $siteId = SITE_ID,
        $params = [
            'cacheTime' => 3600,
            'cacheID' => null,
            'cacheInit' => null,
        ]
    ) {
        if (!$enable) {
            return false;
        }
        $config = Config::getInstance();
//        $hl = HLHelpers::getInstance();
//        $hlDomain = $config->get('highload_domains');
        $logicSubdomain = $config->get('logic_subdomain');
        $typeSubdomain = $config->get('type_subdomain');

        if ($logicSubdomain === self::LOGIC_SUBDOMAIN) {
            $fullHost = \preg_replace('#^(www\.)#i','', $this->getHttpHost());
            $mainHost = $config->get('domain_default');
            $subHost = \str_replace($mainHost,'', $fullHost);
            if($subHost) {
                $subHost = \trim($subHost, '.');
            } else {
                $subHost = 'main';
            }
//            $this->domains = $hl->getElementList($hlDomain, [
//                'UF_SITE_ID' => $siteId,
//                'UF_DOMAIN' => $mainHost,
//                'UF_SUBDOMAIN' => $subHost,
//                'UF_ACTIVE' => 1,
//            ]);
            $this->domains = Domains::getAll(
                DomainEntity::fromArrayHl([
                    'UF_SITE_ID' => $siteId,
                    'UF_DOMAIN' => $mainHost,
                    'UF_SUBDOMAIN' => $subHost,
                    'UF_ACTIVE' => 1,
                ])
            );
            if ($this->domains) {
                foreach ($this->domains as $key => $domain) {
                    $subDomain = '';
                    if ($domain->subDomain) {
                        $subDomain = $domain->subDomain . '.';
                    }
                    $this->domainToLang[$subDomain . $domain->domain] = $domain->lang;
                    $this->domains[$subDomain . $domain->domain] = $domain;
                    unset($this->domains[$key]);
                }
                $scopeHost = "{$subHost}.{$mainHost}";
                if (isset($this->domains[$scopeHost])) {
                    $this->currentDomain = $this->domains[$scopeHost];
                    $this->subdomain = $this->domainToLang[$scopeHost];
                }
            }

//            if (!$this->isSupportHost($this->getHttpHost())) {
//                \CHTTP::SetStatus('404 Not Found');
//            }

        } elseif ($logicSubdomain === self::LOGIC_DIRECTORY) {
            $mainHost = \preg_replace('#^(www\.)#i','', $this->getHttpHost());
//            $this->domains = $hl->getElementList($hlDomain, [
//                'UF_SITE_ID' => $siteId,
//                'UF_DOMAIN' => $mainHost,
////                'UF_SUBDOMAIN' => $subHost,
//                'UF_ACTIVE' => 1,
//            ]);

            $subDomainRouter = '';
            $currentRouter = UrlRewriter::getCurrent();
            if($currentRouter && preg_match($currentRouter['CONDITION'], $_SERVER["REQUEST_URI"], $matches)) {
//                var_dump($matches);
                $subDomainRouter = $matches['subdomain'] ?? static::DEFAULT_SUBDOMAIN;
            }

            if ($currentRouter['RULE'] === '/$2/index.php') {
                $sbCurrentRouter = UrlRewriter::getByPath(
                    str_replace('$2', $subDomainRouter, $currentRouter['RULE'])
                );
                if ($sbCurrentRouter && $sbCurrentRouter !== $currentRouter) {
                    $currentRouter = $sbCurrentRouter;
                    unset($sbCurrentRouter);
                    if(preg_match($currentRouter['CONDITION'], $_SERVER["REQUEST_URI"], $matches)) {
                        $subDomainRouter = $matches['subdomain'] ?? static::DEFAULT_SUBDOMAIN;
                    } else {
                        $subDomainRouter = '';
                    }
                }
            }

            if (!$subDomainRouter) {
                $subDomainRouter = static::DEFAULT_SUBDOMAIN;
            }

            $currentDomain = Domains::getOne(
                DomainEntity::fromArrayHl([
                    'UF_SITE_ID' => $siteId,
                    'UF_DOMAIN' => $mainHost,
                    'UF_SUBDOMAIN' => $subDomainRouter,
//                    'UF_SUBDOMAIN' => $subHost,
                    'UF_ACTIVE' => 1,
                ])
            );

//            if (!$currentDomain) {
//                $currentDomain = Domains::getOne(
//                    DomainEntity::fromArrayHl([
//                        'UF_SITE_ID' => $siteId,
//                        'UF_DOMAIN' => $mainHost,
//                        'UF_SUBDOMAIN' => static::DEFAULT_SUBDOMAIN,
//                        'UF_ACTIVE' => 1,
//                    ])
//                );
//            }

            if ($currentDomain) {
                $this->currentDomain = $currentDomain;
                $this->subdomain = $currentDomain->lang;
                $this->domainToLang[$currentDomain->domain] = $currentDomain;
            }

//            if ($this->domains) {
//                $currentRouter = UrlRewriter::getCurrent();
//                $subDomainRouter = null;
//                if(!$currentRouter) {
//                    $arSubdomains = array_column($this->domains, 'subDomain');
//                    $arSubdomains = ArrayHelper::removeByValue($arSubdomains, ['main']);
//                    if($arSubdomains) {
//                        $strSubdomains = implode('|', $arSubdomains);
//                        if(preg_match("#^/({$strSubdomains})/#i", $_SERVER["REQUEST_URI"], $matches)) {
//                            $subDomainRouter = $matches[1];
//                        }
//                    }
//                } elseif($currentRouter && preg_match($currentRouter['CONDITION'],$_SERVER["REQUEST_URI"], $matches)) {
//                    $subDomainRouter = $matches['subdomain'];
//                }
//                foreach ($this->domains as $domain) {
//                    $subDomainName = $domain->subDomain;
//                    if ($subDomainName === $subDomainRouter) {
//                        $this->currentDomain = $domain;
//                        $this->subdomain = $domain->lang;
//                        $_SERVER["REQUEST_URI"] = str_replace("/{$subDomainName}/", '/', $_SERVER["REQUEST_URI"]);
//                        $this->domainToLang[$domain->domain] = $domain;
//                        break;
//                    } elseif (!$subDomainRouter && $subDomainName === 'main') {
//                        $this->currentDomain = $domain;
//                        $this->subdomain = $domain->lang;
//                        $this->domainToLang[$domain->domain] = $domain;
//                        break;
//                    }
//                }
//            }
        }

        if (!$this->currentDomain) {
            return null;
        }

//        if (!$this->domains) {
//            return null;
//        }

        $this->mainHost = $mainHost;

//         if (!$this->isSupportHost($this->getHttpHost())) {
//            \CHTTP::SetStatus('404 Not Found');
//         }

        if($this->subdomain === 'redirect') {
            if (isset($this->domainToLang[$scopeHost]) && $this->domainToLang[$scopeHost]->lang === 'redirect') {
                $u = $this->redirectDomainProcess();
                if (!$u) {
                    return null;
                }
            } elseif ($typeSubdomain === self::TYPE_VIRTUAL) {
                return $this->virtualDomainProcess();
            }
        }

        $GLOBALS[$this->getGlobalKey()] = $this->subdomain;
        $this->setCookie($this->subdomain);
        $this->checkLang();

        return $this->currentDomain;
    }

    /**
     * @return void
     */
    public function checkLang()
    {
        if (Config::getInstance()->get('enable_multilang') === 'Y') {
            if ($this->currentDomain->lang) {
                $lang = $this->currentDomain->lang;
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
        if (\preg_match('#(\w+\.\w+)$#', $url, $match)) {
            $host = $match[1];
        }
        return $host;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        if (\CMain::IsHTTPS()) {
            return 'https';
        }
        return 'http';
    }

    /**
     * @param string $key
     * @param array $subdomain
     * @return void
     */
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
        $currentPage = $APPLICATION->GetCurUri();

        $subdomain = $this->searchSubdomain();
        $subDomainMaps = Config::getInstance()->get('mapping_list');
        if ($subDomainMaps) {
            $subDomainMaps = unserialize($subDomainMaps, ['allowed_classes' => false]);
            foreach ($subDomainMaps as $subDomainMap) {
                if ($subDomainMap['KEY'] === $subdomain) {
                    $subdomain = $subDomainMap['SUBNAME'];
                    break;
                }
            }
        }

        $isSupport = false;
        $redirectHost = "{$subdomain}.{$this->mainHost}";
        $supportDomains = $this->getDomainList();
        if ($supportDomains) {
            foreach ($supportDomains as $sValue) {
                $host = '';
                if ($sValue['UF_SUBDOMAIN'])
                    $host .= "{$sValue['UF_SUBDOMAIN']}.";
                if ($sValue['UF_DOMAIN'])
                    $host .= $sValue['UF_DOMAIN'];
                if ($host === $redirectHost) {
                    $isSupport = true;
                    break;
                }
            }
        }

        if ($this->strictMode && !$isSupport) {
            return '';
        }

        $this->setCookie($subdomain);
        $url = $this->getProtocol() . '://' . $redirectHost . $currentPage;
        if ($redirect) LocalRedirect($url);
        return $url;
    }

    /**
     * @return bool
     */
    public function virtualDomainProcess()
    {
        $subdomain = $this->searchSubdomain();
        $subDomainMaps = Config::getInstance()->get('mapping_list');
        if ($subDomainMaps) {
            $subDomainMaps = \unserialize($subDomainMaps, ['allowed_classes' => false]);
            foreach ($subDomainMaps as $subDomainMap) {
                if ($subDomainMap['KEY'] === $subdomain) {
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
                if ($host === $redirectHost) {
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

        if ($this->strictMode && !$isSupport) {
            return false;
        }

        return $isSupport;
    }

    /**
     * @param string $subDomain
     * @param string|null $mainHost
     * @return string
     */
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
        $subdomain = $this->searchSubdomain();
        if (!$subdomain) return false;
        $fullDomain = $this->getFullDomain($subdomain);
        if (!\in_array($fullDomain, $this->domainToLang)) {
            return false;
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
        $cookie = \mb_strtolower(htmlspecialcharsbx($cookie));
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
        if ($config->get('type_subdomain') === 'city') {
            return $record->getCityCode();
        }
        return $record->getCountryCode();
    }

    /**
     * @return array|DomainEntity
     */
    public function getCurrent()
    {
        return $this->currentDomain;
    }

    /**
     * Возвращает свойство текущего поддомена
     * @param string $prop
     * @return mixed|null
     */
    public function getCurrentProperty(string $prop)
    {
        $current = static::getCurrent();
        if (!$current) {
            return null;
        }
        return $current->$prop ?? null;
    }

    /**
     * Check cache for host
     * @param array $params cacheTime|cacheID|cacheInit (CPHPCache::InitCache)
     * @return boolean
     */
    private function getCache($params)
    {
        if (!$params['cacheID']) $params['cacheID'] = \md5($this->getHttpHost());
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
        if (!$params['cacheID']) $params['cacheID'] = \md5($this->getHttpHost());
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
        $host = \str_replace($mainHost, '', $host);
        if (!$host) {
            return $this->defaultVal;
        }
        \preg_match('#^(.*?)\.#', $host, $matches);
        if ($matches) {
            return $matches[1];
        }
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
            $rsSites = \CSite::GetList(
                $by = "sort",
                $order = "desc",
                ['ACTIVE' => 'Y', 'DEF' => 'Y']
            );
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
     * Is exist current server host support
     * @param string $host server host
     * @param boolean $checkFile check support array from file
     * @return boolean
     */
    public function isSupportHost($host, $checkFile = false)
    {
        if (!$host) return false;
        $cacheParams = [
            'cacheID' => \md5($host . '_check'),
            'cacheTime' => (3600 * 24 * 30),
        ];
        if ($this->cacheEnable && $checkStatus = $this->getCache($cacheParams)) {
            return $checkStatus;
        }
        $arSupportHost = [];
        if (!$this->csite) {
            $rsSites = \CSite::GetList(
                $by = "sort",
                $order = "asc",
                ['ACTIVE' => 'Y', 'DEFAULT' => 'Y']
            );
            if (!$this->csite = $rsSites->Fetch()) {
                return false;
            }
        }
        $domains = $this->csite['DOMAINS'];
        if ($domains) {
            $arSupportHost = \explode(\PHP_EOL, $domains);
            foreach ($arSupportHost as &$sDomain) {
                $sDomain = \trim($sDomain);
            }
            unset($sDomain);
        }

        $checkStatus = \in_array($host, $arSupportHost);
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
            if($application) {
                $context = $application->getContext();
                $context->setLanguage($lang);
            }
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
     * @deprecated
     */
    public function getLangList()
    {
        return ['en', 'ru'];
    }

    public function getProperties($hlId, $siteId = SITE_ID)
    {
        $host = $this->getHttpHost();
        $arHost = \explode('.', $host);
        if (\count($arHost) > 2) {
            $subHost = $arHost[0];
            $host = \str_replace($subHost . '.', '', $host);
        } else {
            $subHost = '';
        }

        $domain = Domains::getOne(
            DomainEntity::fromArrayHl([
                'UF_SITE_ID' => $siteId,
                'UF_SUBDOMAIN' => $subHost,
                'UF_DOMAIN' => $host,
            ])
        );

//        $domain = HLHelpers::getInstance()->getElementList($hlId, [
//            'UF_SITE_ID' => $siteId,
//            'UF_SUBDOMAIN' => $subHost,
//            'UF_DOMAIN' => $host,
//        ]);
//        if (empty($domain[0])) return false;
        return $domain ?? false;
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

    /**
     * Get all subdomains
     * @return array
     */
    public function getDomainList($siteId = SITE_ID)
    {
        if (!$this->domainList) {
//            $this->domainList = HLHelpers::getInstance()->getElementList(
//                Config::getInstance()->get('highload_domains'),
//                [
//                    'UF_SITE_ID' => $siteId,
//                ]
//            );
            $this->domainList = Domains::getAll(
                DomainEntity::fromArrayHl([
                    'UF_SITE_ID' => $siteId,
                ])
            );
        }

        return $this->domainList ?: [];
    }

    /**
     * @param callable $callbackFilter
     * @return array
     */
    public function getDomainByFilter($callbackFilter)
    {
        $result = array_filter(
            $this->getDomainList(),
            $callbackFilter
        );
        return $result ? current($result)->toArray() : [];
    }

    /**
     * Get route by subdomain
     * @param string $url
     * @param array|null $arSubdomain
     * @return string
     * @since 1.1.0
     */
    public function getRoute(string $url, ?array $arSubdomain = null)
    {
        if ($arSubdomain === null) {
            $arSubdomain = $this->getCurrent();
            if ($arSubdomain) {
                $arSubdomain = $arSubdomain->toArray();
            }
        }

        if($arSubdomain['UF_SUBDOMAIN'] === 'main') {
            return $url;
        }
        $config = \Dev2fun\MultiDomain\Config::getInstance();
        $arUrl = \parse_url($url);
        if($config->get('logic_subdomain') === self::LOGIC_DIRECTORY) {
            $arUrl['path'] = LinkReplace::getReplacePath($url, $arSubdomain);
        } elseif ($config->get('logic_subdomain') === self::LOGIC_SUBDOMAIN) {
            $arUrl['host'] = "{$arSubdomain['UF_SUBDOMAIN']}.{$arSubdomain['UF_DOMAIN']}";
        } else {
            return $url;
        }

        return LinkReplace::buildUrl($arUrl);
    }
}
