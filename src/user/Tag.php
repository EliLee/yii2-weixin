<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/3
 * Time: 14:20
 */

namespace elilee\wx\user;


use elilee\wx\accessToken\AccessToken;
use elilee\wx\core\Driver;
use elilee\wx\core\Exception;

class Tag extends Driver
{
    //标签管理
    const API_CREATE_URL = "https://api.weixin.qq.com/cgi-bin/tags/create?access_token=";
    const API_LIST_URL = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token=";
    const API_UPDATE_URL = "https://api.weixin.qq.com/cgi-bin/tags/update?access_token=";
    const API_DELETE_URL = "https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=";
    const API_FOLLOWERS_URL = "https://api.weixin.qq.com/cgi-bin/user/tag/get?access_token=";
    //用户管理
    const API_BATCH_TAG_URL = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=";
    const API_UN_BATCH_TAG_URL = "https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token=";
    const API_USER_TAGS_URL = "https://api.weixin.qq.com/cgi-bin/tags/getidlist?access_token=";


    private $accessToken;


    static $errors = [
        -1	=>"系统繁忙",
        45056=>"创建的标签数过多，请注意不能超过100个",
        45157=>"标签名非法，请注意不能和其他标签重名",
        45158=>"标签名长度超过30个字节",
        45058=>"不能修改0/1/2这三个系统默认保留的标签",
        45057=>"该标签下粉丝数超过10w，不允许直接删除",
        40032=>"每次传入的openid列表个数不能超过50个",
        45159=>"非法的标签",
        45059=>"有粉丝身上的标签数已经超过限制，即超过20个",
        40003=>"传入非法的openid",
        49003=>"传入的openid不属于此AppID",
    ];
    public function init()
    {
        parent::init();
        $this->httpClient->formatters = ['uncodeJson'=>'elilee\wx\helper\JsonFormatter'];
        $this->accessToken =(new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();
    }

    /**
     * 创建一个标签
     * @param $tag
     * @return mixed
     * @throws Exception
     */
    public function create($tag){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_CREATE_URL . $this->accessToken)
            ->setMethod('post')
            ->setData([
                'tag'=>['name'=>$tag]
            ])
            ->setFormat('uncodeJson')
            ->send();
        $data = $response->getData();
        if(isset($data['errcode'])){
            throw new Exception(self::$errors[$data['errcode']],$data['errcode']);
        }
        return $data['tag'];
    }

    /**
     * 列出所有标签列表 默认有个星标组
     * @return mixed
     */
    public function ls(){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_LIST_URL. $this->accessToken)
            ->setMethod('get')
            ->send();
        $data = $response->getData();
        return $data['tags'];
    }

    /**
     * 修改一个已经存在的标签
     * @param $tagId 要修改的标签ID
     * @param $newName 新标签的名字
     * @return bool
     * @throws Exception
     */
    public function update($tagId,$newName){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_UPDATE_URL. $this->accessToken)
            ->setMethod('post')
            ->setData([
                'tag'=>[
                    'id'=>$tagId,
                    'name'=>$newName
                ]
            ])
            ->setFormat('uncodeJson')
            ->send();
        $data = $response->getData();
        if(isset($data['errcode']) && $data['errcode'] !== 0){
            throw new Exception(self::$errors[$data['errcode']],$data['errcode']);
        }
        return true;
    }

    /**
     * 删除一个标签
     * @param $tagId
     * @return bool
     * @throws Exception
     */
    public function delete($tagId){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_DELETE_URL.$this->accessToken)
            ->setMethod('post')
            ->setData(['tag'=>['id'=>$tagId]])
            ->setFormat('uncodeJson')
            ->send();
        $data = $response->getData();

        if(isset($data['errcode']) && $data['errcode'] !== 0){
            throw new Exception(self::$errors[$data['errcode']],$data['errcode']);
        }
        return true;
    }

    /**
     * 获取当前标签下的粉丝列表
     * @param $tagId
     * @param $nextOpenId
     * @return mixed
     */
    public function followers($tagId,$nextOpenId=''){
        $response= $this->httpClient->createRequest()
            ->setUrl(self::API_FOLLOWERS_URL . $this->accessToken)
            ->setMethod('post')
            ->setData(['tagid'=>$tagId,'next_openid'=>$nextOpenId])
            ->setFormat('uncodeJson')
            ->send();
        $data = $response->getData();

        return $data;
    }

    /**
     * 给多个用户绑定标签
     * @param $openIds
     * @param $tagId
     * @return bool
     * @throws Exception
     */
    public function batchTagToUser($openIds,$tagId){
        $response= $this->httpClient->createRequest()
            ->setUrl(self::API_BATCH_TAG_URL . $this->accessToken)
            ->setMethod('post')
            ->setData(['openid_list'=>$openIds,'tagid'=>$tagId])
            ->setFormat('uncodeJson')
            ->send();
        $data = $response->getData();

        if(isset($data['errcode']) && $data['errcode']==0){
            return true;
        }else{
            throw new Exception(self::$errors[$data['errcode']],$data['errcode']);
        }
    }

    /**
     * 给多个用户取消标签
     * @param $openIds
     * @param $tagId
     * @return bool
     * @throws Exception
     */
    public function unBatchTagFronUser($openIds,$tagId){
        $response= $this->httpClient->createRequest()
            ->setUrl(self::API_UN_BATCH_TAG_URL . $this->accessToken)
            ->setMethod('post')
            ->setData(['openid_list'=>$openIds,'tagid'=>$tagId])
            ->setFormat('uncodeJson')
            ->send();
        $data = $response->getData();

        if(isset($data['errcode']) && $data['errcode']==0){
            return true;
        }else{
            throw new Exception(self::$errors[$data['errcode']],$data['errcode']);
        }
    }

    /**
     * 获取一个用户上的标签列表
     * @param $openId
     * @return mixed
     */
    public function userTags($openId){
        $response= $this->httpClient->createRequest()
            ->setUrl(self::API_USER_TAGS_URL . $this->accessToken)
            ->setMethod('post')
            ->setData(['openid'=>$openId])
            ->setFormat('uncodeJson')
            ->send();
        $data = $response->getData();

        return $data['tagid_list'];
    }
}