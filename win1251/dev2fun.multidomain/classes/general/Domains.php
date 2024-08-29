<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 1.2.0
 * @since 1.2.0
 */

namespace Dev2fun\MultiDomain;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Domains
{
    /**
     * @return string
     */
    public static function getMainHost()
    {
        return Config::getInstance()->get('domain_default');
    }

    /**
     * @return string
     */
    public static function getSubHost()
    {
        $fullHost = \preg_replace('#^(www\.)#i','', SubDomain::getInstance()->getHttpHost());
        $mainHost = static::getMainHost();
        $subHost = \str_replace($mainHost,'', $fullHost);
        if($subHost) {
            $subHost = \trim($subHost, '.');
        } else {
            $subHost = 'main';
        }
        return $subHost;
    }

    /**
     * @param DomainEntity $domainFilters
     * @return DomainEntity|null
     */
    protected static function onBeforeFindDomain(DomainEntity $domainFilters): ?DomainEntity
    {
        $domains = null;
        $event = new Event(Base::$module_id, 'onBeforeFindDomain', [
            'domains' => $domains,
            'filters' => $domainFilters,
        ]);
        $event->send();
        foreach ($event->getResults() as $eventResult) {
            if ($eventResult->getType() === EventResult::SUCCESS) {
                ['domains' => $domains] = $eventResult->getModified();
            }
        }

        return $domains;
    }

    /**
     * @param DomainEntity $domainFilters
     * @return DomainEntity[]|null
     */
    protected static function onBeforeFindDomains(DomainEntity $domainFilters): ?array
    {
        $domains = null;
        $event = new Event(Base::$module_id, 'onBeforeFindDomains', [
            'domains' => $domains,
            'filters' => $domainFilters,
        ]);
        $event->send();
        foreach ($event->getResults() as $eventResult) {
            if ($eventResult->getType() === EventResult::SUCCESS) {
                ['domains' => $domains] = $eventResult->getModified();
            }
        }

        return $domains;
    }

    /**
     * @param DomainEntity $domainFilters
     * @return DomainEntity[]|array
     */
    public static function getAll(DomainEntity $domainFilters): array
    {
        $domains = static::onBeforeFindDomains($domainFilters);
        if ($domains === null) {
            $resArray = HLHelpers::getInstance()
                ->getElementList(
                    Config::getInstance()->get('highload_domains'),
                    $domainFilters->toFilterArray()
                );
            if ($resArray) {
                foreach ($resArray as $item) {
                    $domains[] = DomainEntity::fromArrayHl($item);
                }
            }
        }

        return $domains ?: [];
    }

    /**
     * @param DomainEntity $domainFilters
     * @return DomainEntity|null
     */
    public static function getOne(DomainEntity $domainFilters): ?DomainEntity
    {
        $domains = static::onBeforeFindDomain($domainFilters);
        if ($domains === null) {
            $item = HLHelpers::getInstance()
                ->getElement(
                    Config::getInstance()->get('highload_domains'),
                    $domainFilters->toFilterArray()
                );
            if ($item) {
                $domains = DomainEntity::fromArrayHl($item);
            }
        }

        return $domains ?: null;
    }
}
