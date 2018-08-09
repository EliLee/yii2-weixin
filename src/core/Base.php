<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/2
 * Time: 11:19
 */

namespace elilee\wx\core;

use elilee\wx\accessToken\AccessToken;
use yii\base\Exception;

class Base extends Driver
{
    const BASE_IP_API = "https://api.weixin.qq.com/cgi-bin/getcallbackip";

    public function getValidIps(){
        $access = new AccessToken([
            'conf'=>$this->conf,
            'httpClient'=>$this->httpClient,
            'extra'=>[]]);
        $accessToken =$access->getToken();

        $params=['access_token'=>$accessToken];
        $response =$this->httpClient->createRequest()
            ->setUrl(self::BASE_IP_API)
            ->setMethod('get')
            ->setData($params)
            ->send();
        $data = $response->getData();
        if(!isset($data["ip_list"])){
            throw new Exception($data['errmsg'],$data['errcode']);
        }
        return $data["ip_list"];
    }
}