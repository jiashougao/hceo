<?php

namespace org\helper;

/**
 * 字符串操作
 *
 * Class StringHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class StringHelper
{
    public static function xml2obj($xml,$return_array = true){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$xml,true)){
            xml_parser_free($xml_parser);
            return false;
        }else{
            libxml_disable_entity_loader(true);
            return maybe_json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), $return_array);
        }
    }

    /**
     * object|array 转换成xml
     * @param array|object $parameter
     * @return string|null
     */
    public static function obj2xml($parameter){
        if(!$parameter){
            return null;
        }

        if(is_object($parameter)){
            $parameter = get_object_vars($parameter);
        }

        if(!is_array($parameter)){
            return null;
        }
        $xml = "<xml>";
        foreach ($parameter as $key=>$val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 生成唯一键
     *
     * @return string
     */
    public static function uuid() {
        $charid = md5(uniqid(mt_rand(), true));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }

    /**
     * 生成唯一的GUID
     *
     * @return string
     */
    public static function keyGen() {
        return str_replace('-','',substr(self::uuid(),1,-1));
    }
}