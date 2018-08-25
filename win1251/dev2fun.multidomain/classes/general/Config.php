<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 11.01.2018
 * Time: 22:24
 */

namespace Dev2fun\MultiDomain;


use Bitrix\Main\Config\Option;

class Config
{
	private $options;

	private static $instance;

	/**
	 * Singleton instance.
	 * @return self
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	public function init() {
		$this->options = Option::getForModule('dev2fun.multidomain');
		foreach ($this->options as $k=>&$option) {
			switch ($k) {
				case 'exclude_path' :
					$option = unserialize($option);
					break;
			}
		}
	}

	public function get($name) {
		return $this->options[$name];
	}

	public function set($name, $value) {
		$this->options[$name] = $value;
	}

	public function setAll($arOption) {
		$this->options = array_merge(
			$this->options,
			$arOption
		);
	}
}