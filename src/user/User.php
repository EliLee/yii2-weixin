<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/3
 * Time: 11:14
 */

namespace elilee\wx\user;


use elilee\wx\accessToken\AccessToken;
use elilee\wx\core\Driver;
use yii\helpers\Json;
use yii\httpclient\Client;

class User extends Driver
{
    //http请求方式: GET
    const API_USER_INFO_URL = "https://api.weixin.qq.com/cgi-bin/user/info?lang=zh_CN&access_token=";
    //http请求方式: POST
    const API_BATCH_USER_INFO_URL = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=";

    const API_USER_LIST = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=";
    //黑名单相关
    const API_BLACK_LIST_URL = "https://api.weixin.qq.com/cgi-bin/tags/members/getblacklist?access_token=";
    const API_BATCH_BLACK_URL = "https://api.weixin.qq.com/cgi-bin/tags/members/batchblacklist?access_token=";
    const API_UN_BATCH_BLACK_URL = "https://api.weixin.qq.com/cgi-bin/tags/members/batchunblacklist?access_token=";

    private $accessToken;

    public function init()
    {
        parent::init();
        $this->accessToken = (new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();
    }

    /**
     * 根据openId 获得一个会员信息
     */
    public function info($openId){
        $response =$this->httpClient->createRequest()
            ->setUrl(self::API_USER_INFO_URL.$this->accessToken."&openid=".$openId)
            ->setMethod('get')
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        return Json::decode($response->getContent());
    }

    /**
     * 批量获取会员信息
     */
    public function batchInfo($openIds=[]){
        $userList = array_map(function($openId){
            return [
                'openid'=>$openId,
                'land'=>'zh_CN'
            ];
        },$openIds);

        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_BATCH_USER_INFO_URL.$this->accessToken)
            ->setMethod('post')
            ->setData(['user_list'=>$userList])
            ->setFormat(Client::FORMAT_JSON)
            ->send();

        $data = $response->getData();

        return $data['user_info_list'];

    }
    /**
     * 用户列表
     */
    public function ls($nextOpenId=null){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_USER_LIST.$this->accessToken)
            ->setData([
                'next_openid'=>$nextOpenId
            ])
            ->setMethod('get')
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        return $response->getData();
    }

    /**
     * 获取黑名单列表 一次最多拉去1w个
     * @param string $nextOpenId
     * @return mixed
     */
    public function userInBlock($nextOpenId = ''){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_BLACK_LIST_URL.$this->accessToken)
            ->setData([
                'next_openid'=>$nextOpenId
            ])
            ->setMethod('post')
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        return $response->getData();
    }

    /**
     * 批量拉黑一批用户
     * @param array $openIds
     * @return bool
     */
    public function batchUsersToBlack($openIds = []){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_BATCH_BLACK_URL.$this->accessToken)
            ->setData([
                'openid_list'=>$openIds
            ])
            ->setMethod('post')
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        $data = $response->getData();

        if(isset($data['errcode']) && $data['errcode']==0){
            return true;
        }else{
            throw new Exception(self::$errors[$data['errcode']],$data['errcode']);
        }
    }

    /**
     * 取消拉黑用户
     * @param array $openIds
     * @return bool
     */
    public function unBatchUsersFromBlack($openIds=[]){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_UN_BATCH_BLACK_URL.$this->accessToken)
            ->setData([
                'openid_list'=>$openIds
            ])
            ->setMethod('post')
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        $data = $response->getData();

        if(isset($data['errcode']) && $data['errcode']==0){
            return true;
        }else{
            throw new Exception(self::$errors[$data['errcode']],$data['errcode']);
        }
    }


}