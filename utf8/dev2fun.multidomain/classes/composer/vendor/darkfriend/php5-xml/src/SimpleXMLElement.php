<?php

namespace darkfriend\helpers;

/**
 * Class SimpleXMLElement
 * @package darkfriend\helpers
 * @author darkfriend <hi@darkfriend.ru>
 * @version 1.0.0
 */
class SimpleXMLElement extends \SimpleXMLElement
{
    public function addCData($cdata_text)
    {
        $node = \dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }
}