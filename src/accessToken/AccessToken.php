<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/2
 * Time: 10:39
 */

namespace elilee\wx\accessToken;

use Yii;
use elilee\wx\core\Driver;
use yii\base\Exception;

class AccessToken extends Driver
{
    //获取access_token的接口地址
    const API_TOKEN_GET = 'https://api.weixin.qq.com/cgi-bin/token';
    //存放access_token的缓存
    protected $cacheKey = 'wx-access-token';

    /**
     * @param bool $cacheRefresh 是否缓存
     * @return mixed
     * @throws Exception
     */
    public function getToken($cacheRefresh=false){
        if($cacheRefresh == true){
            Yii::$app->cache->delete($this->cacheKey);
        }

        $data = Yii::$app->cache->get($this->cacheKey);
        if($data ==false){
            $token = $this->getTokenFromServer();
            $data = $token['access_token'];
            Yii::$app->cache->set($this->cacheKey,$data,$token['expires_in']-600);
        }

        return $data;
    }

    /**
     * 从服务器上获得access_token
     * @return mixed
     * @throws Exception
     */
    public function getTokenFromServer(){
        $params = [
            'grant_type' => 'client_credential',
            'appid'      => $this->conf['app_id'],
            'secret'     => $this->conf['secret'],
        ];

        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_TOKEN_GET)
            ->setMethod('get')
            ->setData($params)
            ->send();
        $data = $response->getData();

        if(!isset($data['access_token'])){
            throw new Exception($data['errmsg'],$data['errcode']);
        }

        return $data;
    }
}