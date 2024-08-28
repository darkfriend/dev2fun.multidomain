<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 1.2.0
 * @since 1.2.0
 */

namespace Dev2fun\MultiDomain;

use Bitrix\Main\Type\Contract\Arrayable;

class DomainEntity extends Entity implements Arrayable
{
    /** @var int|null */
    public $id;
    /** @var string|null */
    public $siteId;
    /** @var bool|null */
    public $active;
    /** @var string|null */
    public $name;
    /** @var string|null */
    public $domain;
    /** @var string|null */
    public $subDomain;
    /** @var string|null */
    public $codeCounters;
    /** @var string|null */
    public $metaTags;
    /** @var string|null */
    public $lang;
    /** @var array|null */
    public $options;

    /**
     * @param int|null $id
     * @param string|null $siteId
     * @param bool|null $active
     * @param string|null $name
     * @param string|null $domain
     * @param string|null $subDomain
     * @param string|null $codeCounters
     * @param string|null $metaTags
     * @param string|null $lang
     * @param array|null $options
     */
    public function __construct(
        ?int $id = null,
        ?string $siteId = null,
        ?bool $active = null,
        ?string $name = null,
        ?string $domain = null,
        ?string $subDomain = null,
        ?string $codeCounters = null,
        ?string $metaTags = null,
        ?string $lang = null,
        ?array $options = null
    ) {
        $this->id = $id;
        $this->siteId = $siteId;
        $this->active = $active;
        $this->name = $name;
        $this->domain = $domain;
        $this->subDomain = $subDomain;
        $this->codeCounters = $codeCounters;
        $this->metaTags = $metaTags;
        $this->lang = $lang;
        $this->options = $options;
    }

    /**
     * Возвращает объект из массива
     * @param array $var
     * @return self
     */
    public static function fromArray(array $var): self
    {
        return new static(
            $var['id'] ?? null,
            $var['siteId'] ?? null,
            $var['active'] ?? null,
            $var['name'] ?? null,
            $var['domain'] ?? null,
            $var['subDomain'] ?? null,
            $var['codeCounters'] ?? null,
            $var['metaTags'] ?? null,
            $var['lang'] ?? null,
            $var['options'] ?? null
        );
    }

    /**
     * Возвращает объект из массива highloadblock
     * @param array $var
     * @return self
     */
    public static function fromArrayHl(array $var): self
    {
        return new static(
            $var['ID'] ?? null,
            $var['UF_SITE_ID'] ?? null,
            isset($var['UF_ACTIVE']) ? $var['UF_ACTIVE'] === 'Y' || (int)$var['UF_ACTIVE'] === 1 : null,
            $var['UF_NAME'] ?? null,
            $var['UF_DOMAIN'] ?? null,
            $var['UF_SUBDOMAIN'] ?? null,
            $var['UF_CODE_COUNTERS'] ?? null,
            $var['UF_META_TAGS'] ?? null,
            $var['UF_LANG'] ?? null
        );
    }

    /**
     * Возвращает масив для фильтра Highloadblock из объекта
     * @return array
     */
    public function toFilterArray(): array
    {
        return array_filter(
            [
                'ID' => $this->id,
                'UF_SITE_ID' => $this->siteId,
                'UF_ACTIVE' => $this->active,
                'UF_NAME' => $this->name,
                'UF_DOMAIN' => $this->domain,
                'UF_SUBDOMAIN' => $this->subDomain,
                'UF_CODE_COUNTERS' => $this->codeCounters,
                'UF_META_TAGS' => $this->metaTags,
                'UF_LANG' => $this->lang,
            ],
            fn($value) => $value !== null
        );
    }

    /**
     * Возвращает масив highloadblock из объекта
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ID' => $this->id ?? '',
            'UF_SITE_ID' => $this->siteId ?? '',
            'UF_ACTIVE' => $this->active ?? 'N',
            'UF_NAME' => $this->name ?? '',
            'UF_DOMAIN' => $this->domain ?? '',
            'UF_SUBDOMAIN' => $this->subDomain ?? '',
            'UF_CODE_COUNTERS' => $this->codeCounters ?? '',
            'UF_META_TAGS' => $this->metaTags ?? '',
            'UF_LANG' => $this->lang ?? '',
        ];
    }

}
