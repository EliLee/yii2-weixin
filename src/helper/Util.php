<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/8
 * Time: 15:33
 */

namespace elilee\wx\helper;


use yii\base\Component;

class Util extends Component
{
    /**
     * 生成支付签名前相关参数到url的转化
     *
     * @param $params array 相关参数
     * @return string
     */
    static public function paramsToUrl($params){
        $buff = "";
        foreach($params as $k=>$v){
            if($k != "sign" && $v != "" && is_array($v) == false){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff,"&");
        return $buff;
    }
    static public function makeSign($params,$key){
        ksort($params);
        $str = self::paramsToUrl($params);
        $str .= "&key=".$key;
        return strtoupper(md5($str));
    }
}