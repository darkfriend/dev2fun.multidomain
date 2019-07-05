<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 0.1.32
 */

namespace Dev2fun\MultiDomain;


class TemplateSeo extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
	/**
	 * Обработчик события на вход получает имя требуемой функции
	 * @example {=get_city}
	 * @param \Bitrix\Main\Event $event
	 * @return string
	 */
	public static function EventHandler(\Bitrix\Main\Event $event) {
		$parameters = $event->getParameters();
		$functionName = $parameters[0];
		if ($functionName === "get_city") {
			return \Dev2fun\MultiDomain\Base::GetCurrentDomain()['UF_NAME'];
		} else {
			return '';
		}
	}
}