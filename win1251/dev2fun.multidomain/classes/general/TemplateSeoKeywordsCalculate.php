<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 * @since 1.0.0
 */

namespace Dev2fun\MultiDomain;

class TemplateSeoKeywordsCalculate extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
    /**
     * Handler
     * @param array $parameters Function parameters.
     * @return string
     */
    public function calculate(array $parameters = [])
    {
        return SeoReplace::getCalculateSeo(SeoReplace::KEY_KEYWORDS, $parameters);
    }
}
