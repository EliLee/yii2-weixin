<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/6
 * Time: 15:54
 */

namespace elilee\wx\js;

use elilee\wx\accessToken\AccessToken;
use yii\helpers\Json;
use yii\helpers\Url;
use Yii;
use elilee\wx\core\Driver;
use yii\httpclient\Client;

class Js extends Driver
{
    /**
     * 在页面中使用
     * <script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js">
     * <script>
     * wx.config(<?=$js->buildConfig(['chooseImage'],true); ?>);
     *
     * wx.ready(function(){
        console.log('ready');
     * });
     *
     *
     * wx.error(function(){
        console.log(error);
     * });
     *
     * function doit(){

     * wx.chooseImage({
            count: 1,
     *      sizeType: ['original','compressed'],
     *      sourceType:['album','camera'],
     *      success: function(res){
                var localIds =res.localIds;
     *      }
        });
     * }
     * </script>
     *
     *
     */

    const API_TICKET = 'https//api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=';
    private $cacheKey='wx-mp-js-ticket';


    public function init()
    {
        parent::init();
    }

    /**
     * 构建JSSDK配置参数
     * @param array $apis
     * @param bool $debug
     * @return string
     * @throws \yii\base\Exception
     */

    public function buildConfig($apis=[],$debug=false){
        $signPackage = $this->signature();

        $base = [
            'debug'=>$debug
        ];
        $config =array_merge($base,$signPackage,['jsApiList'=>$apis]);

        return Json::encode($config);
    }
    /**
     * 获得jssdk需要的配置参数
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function signature(){
        $url = Url::current([],true);
        $nonce = Yii::$app->security->generateRandomString(32);
        $timestamp =time();
        $ticket = $this->ticket();
        $sign = [
            'appId'=>$this->conf['app_id'],
            'nonceStr' =>$nonce,
            'timestamp' =>$timestamp,
            'url' => $url,
            'signature' => $this->getSignature($ticket,$nonce,$timestamp,$url)
        ];
        return $sign;
    }
    /**
     * 获取签名
     * @param $ticket
     * @param $nonce
     * @param $timestamp
     * @param $url
     * @return string
     */
    public function getSignature($ticket,$nonce,$timestamp,$url){
        return sha1("jsapi_ticket={$ticket}&noncestr={$nonce}&timestamp{$timestamp}&url={$url}");
    }

    public function ticket(){
        $ticket = Yii::$app->cache->get($this->cacheKey);

        if($ticket == false){
            //从服务器获取
            $accessToken = (new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();
            $response = $this->httpClient->createRequest()
                ->setUrl(self::API_TICKET.$accessToken)
                ->setMethod('get')
                ->setFormat(Client::FORMAT_JSON)
                ->send();

            $data = $response->getData();

            $ticket = $data['ticket'];

            Yii::$app->cache->set($this->cacheKey,$ticket,$data['expires_in']-600);

        }
        return $ticket;
    }
}

