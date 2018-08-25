<?php

namespace Dev2fun\MultiDomain;
use GeoIp2\Model\City;
use GeoIp2\Model\Country;
use GeoIp2\Record\Location;

/**
 * Работа с геолокацией
 *
 * @author darkfriend
 * @version 0.0.1
 * @copyright (c) 07.04.2016, darkfriend
 */
class Geo {

    private $reader,
            $record;
    public  $city,
            $country;

	private static $instance;

	/**
	 * Singleton instance.
	 * @return self
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    public function __construct() {
        include(__DIR__.'/../../lib/geoip/vendor/autoload.php');
//        $this->reader = new \GeoIp2\Database\Reader($_SERVER['DOCUMENT_ROOT'].'/upload/geolite/GeoLite2-Country.mmdb');
        $this->reader = new \GeoIp2\Database\Reader(__DIR__.'/../../lib/geoip/db/GeoLite2-City.mmdb');
//        if($this->reader){
//            $this->record = $this->reader->city($_SERVER['HTTP_X_REAL_IP']);
//        }
    }

	/**
	 * Устанавливает IP
	 * @param string $ip
	 * @return $this|bool
	 */
    public function setIp($ip) {
		if(!$ip) return false;
		if($this->reader) {
			$this->record = $this->reader->city($ip);
			return $this;
		}
		return false;
	}

	/**
	 * Возвращает город
	 * @return City|bool
	 */
	public function getCity(){
		if(!$this->record) return false;
		if(!$this->city){
			$this->city = $this->record->city;
		}
		return $this->city;
	}

	/**
	 * Возвращает название города
	 * @param string $lang - символьный код языка
	 * @return string
	 */
	public function getCityName($lang='ru'){
		if(!$this->record) return false;
		$city = $this->getCity();
		if(!$city) return false;
		return $city->names[$lang];
	}

	/**
	 * Возвращает символьный код города в нижнем регистре
	 * @return string
	 */
	public function getCityCode(){
		if(!$this->record) return false;
		$city = $this->getCity();
		if(!$city) return false;
		return mb_strtolower($this->record->subdivisions[0]->isoCode);
	}

	/**
	 * Возвращает страну
	 * @return bool|Country
	 */
	public function getCountry() {
		if(!$this->record) return false;
		if(!$this->country){
			$this->country = $this->record->country;
		}
		return $this->country;
	}

	/**
	 * Возвращает название страны в нижнем регистре
	 * @param string $lang - символьный код языка
	 * @return string
	 */
	public function getCountryName($lang='ru') {
		if(!$this->record) return false;
		$country = $this->getCountry();
		if(!$country) return false;
//		if(!$this->country){
//			$this->country = $this->record->country->names[$lang];
//		}
		return $country->names[$lang];
	}

	/**
	 * Возвращает код страны в нижнем регистре
	 * @return string
	 */
	public function getCountryCode(){
		if(!$this->record) return false;
		$country = $this->getCountry();
		if(!$country) return false;
		return mb_strtolower($country->isoCode);
	}

	/**
	 * Возвращает информацию о местоположении
	 * @return bool|Location
	 */
	public function getLocation() {
		if(!$this->record) return false;
		return $this->record->location;
	}
}
