<?php

namespace app\helpers;

/**
 * Class XmlHelper
 * @package app\helpers
 */
class XmlHelper
{
    /**
     * Конвертация XML документа в массив
     *
     * @param string|\SimpleXMLElement $xml
     * @return array
     * @throws \TypeError
     */
    public static function xml2Array($xml): array
    {
        if (is_string($xml)) {
            $simpleXmlObject = simplexml_load_string($xml);
        } elseif ($xml instanceof \SimpleXMLElement) {
            $simpleXmlObject = $xml;
        } else {
            throw new \TypeError("Argument 1 passed to XmlHelper::xml2Array() must be of the type string or SimpleXMLElement.");
        }
        $jsonString = json_encode($simpleXmlObject);
        return json_decode($jsonString, true);
    }
}