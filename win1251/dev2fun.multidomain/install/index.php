<?
IncludeModuleLangFile(__FILE__);

/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 0.1.32
 */
if(class_exists("dev2fun_multidomain")) return;

use Bitrix\Main\ModuleManager,
	Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\IO\Directory,
	Bitrix\Main\Config\Option;

Loader::registerAutoLoadClasses(
	"dev2fun.multidomain",
	array(
		'Dev2fun\MultiDomain\Base' => 'include.php',
		'Dev2fun\MultiDomain\SubDomain' => 'classes/general/SubDomain.php',
		'Dev2fun\MultiDomain\HLHelpers' => 'lib/HLHelpers.php',
	)
);

class dev2fun_multidomain extends CModule
{
	var $MODULE_ID = "dev2fun.multidomain";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	public function dev2fun_multidomain() {
//		$path = str_replace("\\", "/", __FILE__);
//		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include(__DIR__."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = Loc::getMessage("D2F_MODULE_NAME_MULTIDOMAIN");
		$this->MODULE_DESCRIPTION = Loc::getMessage("D2F_MODULE_DESCRIPTION_MULTIDOMAIN");
		$this->PARTNER_NAME = "dev2fun";
		$this->PARTNER_URI = "http://dev2fun.com/";
	}

	public function DoInstall() {
		global $APPLICATION, $DB;
		if(!check_bitrix_sessid()) return;
		require_once __DIR__.'/../lib/HLHelpers.php';
		$DB->StartTransaction();
		try {
			if(!Loader::includeModule('highloadblock')){
				throw new Exception(Loc::getMessage("NO_INSTALL_HIGHLOADBLOCK"));
			}
			if(!Loader::includeModule('iblock')){
				throw new Exception(Loc::getMessage("NO_INSTALL_IBLOCK"));
			}
			$this->installFiles();
			$this->installDB();
			$this->registerEvents();
			$DB->Commit();
			ModuleManager::registerModule($this->MODULE_ID);
			\CAdminNotify::Add([
				'MESSAGE' => Loc::getMessage('D2F_MULTIDOMAIN_NOTICE_THANKS'),
				'TAG' => $this->MODULE_ID.'_install',
				'MODULE_ID' => $this->MODULE_ID,
			]);
		} catch (Exception $e) {
			$DB->Rollback();
			$GLOBALS['D2F_MULTIDOMAIN_ERROR'] = $e->getMessage();
			$GLOBALS['D2F_MULTIDOMAIN_ERROR_NOTES'] = Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_ERROR_NOTES');
			$APPLICATION->IncludeAdminFile(
				Loc::getMessage("D2F_MULTIDOMAIN_STEP_ERROR"),
				__DIR__."/error.php"
			);
			return false;
		}
		$APPLICATION->IncludeAdminFile(Loc::getMessage("D2F_MULTIDOMAIN_STEP1"), __DIR__."/step1.php");
	}

	public function installFiles() {
		// copy components files
		if(!CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true)){
			CAdminMessage::ShowMessage(Loc::getMessage("ERRORS_SAVE_FILE",array('#DIR#'=>__DIR__."bitrix/components")));
		}
		// copy js files
		if(!CopyDirFiles(__DIR__."/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID, true, true)){
			throw new Exception(Loc::getMessage("ERRORS_SAVE_FILE",array('#DIR#'=>__DIR__."bitrix/js")));
		}
		// copy js files
		if(!CopyDirFiles(__DIR__."/css", $_SERVER["DOCUMENT_ROOT"]."/bitrix/css/".$this->MODULE_ID, true, true)){
			throw new Exception(Loc::getMessage("ERRORS_SAVE_FILE",array('#DIR#'=>__DIR__."bitrix/css")));
		}
		// copy js files
		if(!CopyDirFiles(__DIR__."/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/".$this->MODULE_ID, true, true)){
			throw new Exception(Loc::getMessage("ERRORS_SAVE_FILE",array('#DIR#'=>__DIR__."bitrix/images")));
		}
		// copy admin files
		if(!CopyDirFiles(__DIR__."/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true)){
			throw new Exception(Loc::getMessage("ERRORS_SAVE_FILE", array('#DIR#'=>'bitrix/admin')));
		}
		return true;
	}

	public function installDB() {
		if(!Option::get($this->MODULE_ID,'highload_domains')) {
			$hlId = $this->_installDomains();
			if(!$hlId) throw new Exception(\Dev2fun\MultiDomain\HLHelpers::$LAST_ERROR);
			Option::set($this->MODULE_ID,'highload_domains',$hlId);
		}

		if(!Option::get($this->MODULE_ID,'highload_domains_seo')) {
			$hlId = $this->_installSeo();
			if(!$hlId) throw new Exception(\Dev2fun\MultiDomain\HLHelpers::$LAST_ERROR);
			Option::set($this->MODULE_ID,'highload_domains_seo',$hlId);
		}

		if(!Option::get($this->MODULE_ID,'exclude_path')) {
			$excPath = [
				'(\.php$)',
				'\/(bitrix|local)\/(admin|tools)\/',
			];
			Option::set($this->MODULE_ID,'exclude_path',serialize($excPath));
			Option::set($this->MODULE_ID,'key_ip','HTTP_X_REAL_IP');
		}
		return true;
	}

	private function _installDomains() {
		$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
		$hlId = $hl->create('Dev2funMultiDomain','dev2fun_multidomain');
		if(!$hlId) {
			throw new Exception(\Dev2fun\MultiDomain\HLHelpers::$LAST_ERROR);
		}
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_ACTIVE',
			'USER_TYPE_ID' => 'boolean',
			'SORT' => '50',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SETTINGS' => [
				'DEFAULT_VALUE' => 1,
			],
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_ACTIVE'),
				'en' => 'Active',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_ACTIVE'),
				'en' => 'Active',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_NAME',
			'USER_TYPE_ID' => 'string',
			'SORT' => '100',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'Y',
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_NAME'),
				'en' => 'Name',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_NAME'),
				'en' => 'Name',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_SUBDOMAIN',
			'USER_TYPE_ID' => 'string',
			'SORT' => '200',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_SUBDOMAIN'),
				'en' => 'Subdomain',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_SUBDOMAIN'),
				'en' => 'Subdomain',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_DOMAIN',
			'USER_TYPE_ID' => 'string',
			'SORT' => '300',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_DOMAIN'),
				'en' => 'Main domain',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_DOMAIN'),
				'en' => 'Main domain',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_CODE_COUNTERS',
			'USER_TYPE_ID' => 'string',
			'SORT' => '400',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SETTINGS' => [
				'SIZE' => '30',
				'ROWS' => '10',
			],
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_CODE_COUNTERS'),
				'en' => 'HTML-Code counters',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_LIST_COLUMN_LABEL_UF_CODE_COUNTERS'),
				'en' => 'Counters',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_META_TAGS',
			'USER_TYPE_ID' => 'string',
			'SORT' => '500',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SETTINGS' => [
				'SIZE' => '30',
				'ROWS' => '10',
			],
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_META_TAGS'),
				'en' => 'HTML-code meta tags',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_LIST_COLUMN_LABEL_UF_META_TAGS'),
				'en' => 'Meta tags',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_LANG',
			'USER_TYPE_ID' => 'string',
			'SORT' => '600',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_LANG'),
				'en' => 'Lang',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_EDIT_FORM_LABEL_UF_LANG'),
				'en' => 'Lang',
			],
		]);
		return $hlId;
	}

	private function _installSeo() {
		$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
		$hlId = $hl->create('Dev2funMultiDomainSeo','dev2fun_multidomain_seo');
		if(!$hlId) {
			throw new Exception(\Dev2fun\MultiDomain\HLHelpers::$LAST_ERROR);
		}
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_DOMAIN',
			'USER_TYPE_ID' => 'string',
			'SORT' => '100',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'Y',
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_SEO_EDIT_FORM_LABEL_UF_DOMAIN'),
				'en' => 'Domain',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_SEO_EDIT_FORM_LABEL_UF_DOMAIN'),
				'en' => 'Domain',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_H1',
			'USER_TYPE_ID' => 'string',
			'SORT' => '120',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'EDIT_FORM_LABEL' => [
				'ru' => 'H1',
				'en' => 'H1',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => 'H1',
				'en' => 'H1',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_PATH',
			'USER_TYPE_ID' => 'string',
			'SORT' => '150',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'Y',
			'EDIT_FORM_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_SEO_EDIT_FORM_LABEL_UF_PATH'),
				'en' => 'Path',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => Loc::getMessage('D2F_MULTIDOMAIN_INSTALL_SEO_EDIT_FORM_LABEL_UF_PATH'),
				'en' => 'Path',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_TITLE',
			'USER_TYPE_ID' => 'string',
			'SORT' => '200',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'Y',
			'EDIT_FORM_LABEL' => [
				'ru' => 'Title',
				'en' => 'Title',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => 'Title',
				'en' => 'Title',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_DESCRIPTION',
			'USER_TYPE_ID' => 'string',
			'SORT' => '300',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SETTINGS' => [
				'SIZE' => '30',
				'ROWS' => '10',
			],
			'EDIT_FORM_LABEL' => [
				'ru' => 'Description',
				'en' => 'Description',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => 'Description',
				'en' => 'Description',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_KEYWORDS',
			'USER_TYPE_ID' => 'string',
			'SORT' => '400',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'EDIT_FORM_LABEL' => [
				'ru' => 'Keywords',
				'en' => 'Keywords',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => 'Keywords',
				'en' => 'Keywords',
			],
		]);
		$hl->addField($hlId,[
			'FIELD_NAME' => 'UF_TEXT',
			'USER_TYPE_ID' => 'string',
			'SORT' => '500',
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SETTINGS' => [
				'ROWS' => '15',
			],
			'EDIT_FORM_LABEL' => [
				'ru' => 'SEO-Text',
				'en' => 'SEO-Text',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => 'SEO-Text',
				'en' => 'SEO-Text',
			],
		]);
		return $hlId;
	}

	public function registerEvents() {
		$eventManager = EventManager::getInstance();

		$eventManager->registerEventHandler("main", "OnPageStart", $this->MODULE_ID, "Dev2fun\\MultiDomain\\Base", "InitDomains");
		$eventManager->registerEventHandler("main", "OnEpilog", $this->MODULE_ID, "Dev2fun\\MultiDomain\\Base", "InitSeoDomains");
		$eventManager->registerEventHandler("main", "OnEndBufferContent", $this->MODULE_ID, "Dev2fun\\MultiDomain\\Base", "InitBufferContent");
		$eventManager->registerEventHandler("iblock", "OnTemplateGetFunctionClass", $this->MODULE_ID, "Dev2fun\\MultiDomain\\TemplateSeo", "EventHandler");

		return true;
	}


	public function DoUninstall() {
		global $APPLICATION, $DB;
		if(!check_bitrix_sessid()) return;
		$DB->StartTransaction();
		try {
			if($_REQUEST['UNSTEP']!=2) {
				$APPLICATION->IncludeAdminFile(
					Loc::getMessage("D2F_MODULE_MULTIDOMAIN_UNSTEP0"),
					__DIR__ . "/unstep0.php"
				);
			} else {
				if(!Loader::includeModule('highloadblock')){
					throw new Exception(Loc::getMessage("NO_INSTALL_HIGHLOADBLOCK"));
				}
				if(!Loader::includeModule('iblock')){
					throw new Exception(Loc::getMessage("NO_INSTALL_IBLOCK"));
				}
				$isSave = empty($_REQUEST['SAVE_DATA']) ? false : true;
				if(!$isSave) {
					$this->unInstallDB();
				}
				$this->unRegisterEvents();
				$this->deleteFiles();
				$DB->Commit();
				\CAdminNotify::Add([
					'MESSAGE' => Loc::getMessage('D2F_MULTIDOMAIN_NOTICE_WHY'),
					'TAG' => $this->MODULE_ID.'_uninstall',
					'MODULE_ID' => $this->MODULE_ID,
				]);
				ModuleManager::unRegisterModule($this->MODULE_ID);
			}
		} catch (Exception $e) {
			$DB->Rollback();
			$GLOBALS['D2F_COMPRESSIMAGE_ERROR'] = $e->getMessage();
			$GLOBALS['D2F_COMPRESSIMAGE_ERROR_NOTES'] = Loc::getMessage('D2F_MULTIDOMAIN_UNINSTALL_ERROR_NOTES');
			$APPLICATION->IncludeAdminFile(
				Loc::getMessage("D2F_MULTIDOMAIN_STEP_ERROR"),
				__DIR__."/error.php"
			);
			return false;
		}

		$APPLICATION->IncludeAdminFile(GetMessage("D2F_MULTIDOMAIN_UNSTEP1"), __DIR__."/unstep1.php");
	}

	public function deleteFiles() {
		DeleteDirFilesEx('/bitrix/js/'.$this->MODULE_ID);
		DeleteDirFilesEx('/bitrix/css/'.$this->MODULE_ID);
		DeleteDirFilesEx('/bitrix/images/'.$this->MODULE_ID);
		DeleteDirFiles(__DIR__.'/admin', $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		return true;
	}

	public function unInstallDB() {
		$hlId = Option::get($this->MODULE_ID,'highload_domains');
		$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
		if(!$hl->deleteHighloadBlock($hlId)){
			throw new Exception(Loc::getMessage("D2F_MULTIDOMAIN_UNINSTALL_ERROR_HIGHLOADBLOCK").$hlId);
		}
		$hlId = Option::get($this->MODULE_ID,'highload_domains_seo');
		$hl = \Dev2fun\MultiDomain\HLHelpers::getInstance();
		if(!$hl->deleteHighloadBlock($hlId)){
			throw new Exception(Loc::getMessage("D2F_MULTIDOMAIN_UNINSTALL_ERROR_HIGHLOADBLOCK").$hlId);
		}
		Option::delete($this->MODULE_ID);
		return true;
	}

	public function unRegisterEvents() {
		$eventManager = EventManager::getInstance();

		$eventManager->unRegisterEventHandler('main','OnPageStart',$this->MODULE_ID);
		$eventManager->unRegisterEventHandler('main','OnEpilog',$this->MODULE_ID);
		$eventManager->unRegisterEventHandler('main','OnEndBufferContent',$this->MODULE_ID);
		$eventManager->unRegisterEventHandler('iblock','OnTemplateGetFunctionClass',$this->MODULE_ID);

		return true;
	}
}
