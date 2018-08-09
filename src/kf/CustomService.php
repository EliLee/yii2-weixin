<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/9
 * Time: 16:50
 */

namespace elilee\wx\kf;


use elilee\wx\accessToken\AccessToken;
use elilee\wx\core\Driver;

class CustomService extends Driver
{
    const API_SEND_URL = '';
    private $accessToken;

    public function init()
    {
        parent::init();
        $this->accessToken = (new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();
    }

    /**
     * 给会员发送某个类型的消息
     * @param $openId
     * @param $type
     * @param $data
     * @param array $extra
     * @return bool
     */
    public function send($openId,$type,$data,$extra=[]){
        $params = array_merge(['touser'=>$openId,'msgtype'=>$type],[$type=>$data],$extra);
        $this->httpClient->formatters = ['uncodeJson'=>'elilee\wx\helper\JsonFormatter'];
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_SEND_URL."?access_token={$this->accessToken}")
            ->setMethod('post')
            ->setFormat('uncodeJson')
            ->setData($params)
            ->send();
        if($response->isOk == false){
            throw new Exception('网络问题');
        }
        $result = $response->getData();

        if(isset($result['errcode']) && $result['errcode'] !=0){
            throw new Exception($result['errmsg']);
        }
        return true;
    }
}