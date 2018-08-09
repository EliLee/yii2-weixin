<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/3
 * Time: 10:57
 */

namespace elilee\wx\qrcode;


use elilee\wx\accessToken\AccessToken;
use elilee\wx\core\Driver;
use yii\httpclient\Client;

class Shorturl extends Driver
{
    private $accessToken;
    const API_SHORT_URL = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token=';

    public function init()
    {
        parent::init(); //
        $this->accessToken=(new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();
    }

    public function toShort($longUrl=''){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_SHORT_URL.$this->accessToken)
            ->setMethod('post')
            ->setFormat(Client::FORMAT_JSON)
            ->setData(['action'=>'long2short','long_url'=>$longUrl])
            ->send();
        $data = $response->getData();
        //TODO 要做接口返回错误的判断
        return $data['short_url'];
    }
}