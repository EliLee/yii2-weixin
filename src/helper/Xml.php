<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/2
 * Time: 12:45
 */

namespace elilee\wx\helper;

use Yii;
use SimpleXMLElement;

class Xml
{
    public static function parse($xml) {
        return self::normalize(simplexml_load_string($xml, 'SimpleXMLElement',LIBXML_NOCDATA | LIBXML_NOBLANKS));
    }

    public static function build($data, $root='xml',$item='item',$attr='',$id='id'){
        if (is_array($attr)) {
            $_attr = [];
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<{$root}{$attr}>";
        $xml .= self::data2Xml($data, $item, $id);
        $xml .= "</{$root}>";
        return $xml;
    }
    public static function cdata($string){
        return sprintf('<![CDATA[%s]]>', $string);
    }
    protected static function normalize($obj) {
        $result = null;

        if (is_object($obj)) {
            $obj = (array) $obj;
        }
        if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $res = self::normalize($value);
                if (($key === '@attributes') && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $obj;
        }
        return $result;
    }
    protected static function data2Xml($data, $item = 'item', $id = 'id') {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            if ((is_array($val) || is_object($val))) {
                $xml .= self::data2Xml((array) $val, $item, $id);
            } else {
                $xml .= is_numeric($val) ? $val : self::cdata($val);
            }
            $xml .= "</{$key}>";
        }
        return $xml;
    }
}