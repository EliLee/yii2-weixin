<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/6
 * Time: 14:47
 */

namespace elilee\wx\oauth;

use Yii;
use elilee\wx\core\Driver;
use yii\httpclient\Client;

class OAuth extends Driver
{
    const API_URL='https://open.weixin.qq.com/connect/oauth2/authorize';
    const API_ACCESS_TOKEN_URL ='https://api.weixin.qq.com/sns/oauth2/access_token';
    const API_USER_INFO_URL ='https://api.weixin.qq.com/sns/userinfo';

    public $code =false;
    protected  $accessToken = false;
    protected  $openId = false;

    public function send(){
        $url = self::API_URL."?appid=".$this->conf['app_id']."&redirect_uri=".$this->conf['oauth']['callback']."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        header("location:",$url);
    }

    /**
     * 获取网页授权的accessToken
     * @return bool
     */
    protected function initAccessToken(){
        if($this->accessToken){
            return $this->accessToken;
        }
        $code =$this->getCode(); //用户点击同意授权是回传的一个参数
        $url =self::API_ACCESS_TOKEN_URL ."?appid={$this->conf['app_id']}&secret={$this->conf['secret']}&code={$code}&grant_type=authorization_code";

        $response =$this->httpClient->createRequest()
            ->setMethod('get')
            ->setUrl($url)
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        $accessTokenInfo = $response->getData();

        $this->accessToken =$accessTokenInfo['access_token'];
        $this->openId =$accessTokenInfo['openid'];

    }
    protected function getCode(){
        if($this->code == false){
            $this->code = Yii::$app->request->get('code');
        }
        return $this->code;
    }

    public function user(){
        $this->initAccessToken();
        $url = self::API_USER_INFO_URL."?access_token={$this->accessToken}&openid={$this->openId}&lang=zh_CN";

        $response = $this->httpClient->createRequest()
                    ->setMethod('get')
                    ->setUrl($url)
                    ->setFormat(Client::FORMAT_JSON)
                    ->send();

        return $response->getData();
    }

}