<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/1
 * Time: 18:12
 */

namespace elilee\wx\qrcode;


use elilee\wx\accessToken\AccessToken;
use elilee\wx\core\Driver;
use yii\httpclient\Client;

class Qrcode extends Driver
{
    private $accessToken;

    const API_QRCODE_URL = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=';

    public function init(){
        parent::init();
        $this->accessToken = (new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();
    }

    public function intTemp($seconds =2592000,$val){
        return $this->temp('QR_SCENE',$seconds,['scene_id'=>$val]);
    }

    public function strTemp($seconds=2592000,$val){
        return $this->temp('QR_STR_SCENE',$seconds,['scene_str'=>$val]);
    }

    private function temp($action='QR_SCENE', $seconds=2592000, $scene=['scene_id'=>0]){
        $params = array_merge([
            'expire_seconds'=>$seconds,
            'action_name'=>$action,
            'action_info'=>[
                'scene'=>$scene
            ]
        ]);

        $response = $this->httpClient->createRequest()
            ->setUrl(Qrcode::API_QRCODE_URL.$this->accessToken)
            ->setMethod('post')
            ->setFormat( Client::FORMAT_JSON)
            ->setData($params)
            ->send();

        return $response->getData();
    }

    /**
     * 生成永久二维码
     */
    public function intForver($val){
        return $this->forver('QR_LIMIT_SCENE',['scene_id'=>$val]);
    }
    public function strForver($val){
        return $this->forver('QR_LIMIT_STR_SCENE',['scene_str'=>$val]);
    }
    private function forver($action='QR_LIMIT_SCENE',$scene=['scene_id'=>0]){
        $params = array_merge(['action_name'=>$action,'action_info'=>['scene'=>$scene]]);
        $response =$this->httpClient->createRequest()
            ->setUrl(Qrcode::API_QRCODE_URL.$this->accessToken)
            ->setMethod('post')
            ->setFormat(Client::FORMAT_JSON)
            ->setData($params)->send();
        return $response->getData();
    }

}