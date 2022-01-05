<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 */

namespace Dev2fun\MultiDomain;


use Bitrix\Main\Config\Option;

class Config
{
    /** @var array */
//    private $options;
    /** @var self */
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
     * @param string $name
     * @param mixed $default
     * @param string $siteId
     * @return string
     */
    public function get($name, $default = '', $siteId = SITE_ID)
    {
        $option = Option::get(Base::$module_id, $name, $default, $siteId);
        switch ($name) {
            case 'mapping_list':
            case 'exclude_path':
                if ($option && \is_string($option)) {
                    $option = unserialize($option, ['allowed_classes' => false]);
                    if (key($option) !== 0) {
                        $option = \array_values($option);
                    }
                }
                break;
        }

        return $option;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $siteId
     */
    public function set($name, $value, $siteId = SITE_ID)
    {
        Option::set(Base::$module_id, $name, $value, $siteId);
    }

    /**
     * @param array $arOption
     * @param string $siteId
     */
    public function setAll($arOption, $siteId = SITE_ID)
    {
        foreach ($arOption as $key=>$item) {
            $this->set($key, $item, $siteId);
        }
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function getCommon($name, $default = '')
    {
        return Option::get(Base::$module_id, $name, $default);
    }

    /**
     * @param string $name
     * @param string $value
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function setCommon($name, $value)
    {
        Option::set(Base::$module_id, $name, $value);
    }
}