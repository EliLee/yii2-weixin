<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/3
 * Time: 16:54
 */

namespace elilee\wx\resource;


use elilee\wx\accessToken\AccessToken;
use elilee\wx\core\Driver;
use elilee\wx\core\Exception;
use yii\helpers\Json;
use yii\httpclient\Client;

class Resource extends Driver
{
    private $accessToken;

    const API_MEDIA_UPLOAD_URL = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=';
    const API_MEDIA_GET_URL = "https://api.weixin.qq.com/cgi-bin/media/get";
    const API_FOREVER_MEDIA_UPLOAD_URL = "https://api.weixin.qq.com/cgi-bin/material/add_material";
    const API_NEWS_ADD_URL='https://api.weixin.qq.com/cgi-bin/material/add_news';
    const API_UPDATE_NEWS_URL = 'https://api.weixin.qq.com/cgi-bin/material/update_news?access_token=';
    const API_FOREVER_MEDIA_DELETE_URL = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=';
    const API_FOREVER_MEDIA_TOTAL_URL = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=';
    const API_FOREVER_MEDIA_LIST_URL = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=';


    public function init()
    {
        parent::init();
        $this->accessToken =(new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();

    }

    /**
     * 新增一个临时素菜
     * @param $file
     * @param string $type
     * @return mixed
     * @throws Exception
     */
    public function addTempMedia($file,$type='image'){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_MEDIA_UPLOAD_URL.$this->accessToken.'&type='.$type)
            ->setMethod('post')
            ->addFile('media',$file)
            ->send();
        if($response->isOk ==false){
            throw new Exception('网络问题，没有得到服务器响应');
        }
        $response->setFormat(Client::FORMAT_JSON);
        $result = $response->getData();

        if(isset($result['errcode'])){
            throw new Exception($result['errmsg']);
        }
        return $result['media_id'];
    }

    /**
     * 获取临时素菜
     * @param $mediaId
     * @param bool $savePath
     * @return mixed
     * @throws Exception
     */
    public function getMedia($mediaId, $savePath=false){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_MEDIA_GET_URL)
            ->setMethod('get')
            ->setData([
                'access_token'=>$this->accessToken,
                'media_id'=>$mediaId
            ])
            ->send();
        if($response->isOk ==false){
            throw new Exception('网络问题，没有得到服务器响应');
        }
        $contentType = $response->getHeaders()->get('content-type');


        if($contentType == 'applicationin/json'){
            //报错或者视频url
            $data = $response->getData();
            if(isset($data['errcode'])){
                throw new Exception($data['errmsg']);
            }

            if(isset($data['video_url'])){
                return $data['video_url'];
            }
        }else if($contentType == 'image/jpeg'){
            //图片类型
            header('Content-type:'.$contentType);
            $stream = $response->getContent();
            return $stream;
        }else if(in_array($contentType,['audio/amr','voice/speex'])){
            //音频类型
            $stream = $response->getContent();
            return $stream;
        }

    }

    /**
     * 新增一个永久素菜
     * @param $file
     * @param string $type
     * @return mixed
     * @throws Exception
     * TODO 需要使用认证的公众号验证
     */
    public function addForeverMedia($file,$type='image',$videoFrom=[]){
        $resquest = $this->httpClient->createRequest()
            ->setUrl(self::API_FOREVER_MEDIA_UPLOAD_URL.$this->accessToken.'&type='.$type)
            ->setMethod('post')
            ->addFile('media',$file);

        if($type == 'video'){
            $resquest->addData([
                'description' => Json::encode($videoFrom)
            ]);
        }
        $response = $resquest->send();

        if($response->isOk ==false){
            throw new Exception('网络问题，没有得到服务器响应');
        }
        $response->setFormat(Client::FORMAT_JSON);
        $result = $response->getData();

        if(isset($result['errcode'])){
            throw new Exception($result['errmsg']);
        }
        if($type == 'image'){
            return $result;
        }else{
            return $result['media_id'];
        }

    }

    public function getForeverMedia($mediaId){
//        $response = $this->httpClient->createRequest()
//            ->setMethod('post')
//            ->setUrl(self::API_FOREVER_MEDIA_UPLOAD_URL.$this->accessToken)
//            ->setData([
//
//            ])
    }

    /**
     * 添加一个图文
     * @param array $articles 图文数组 每个1个图文
     * @return mixed
     * @throws Exception
     * TODO 需要使用认证的公众号验证
     */
    public function addNews($articles=[]){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_NEWS_ADD_URL . $this->accessToken)
            ->setMethod('post')
            ->setData(['articles'=>$articles])
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        if($response->isOk == false){
            throw new Exception('网络问题，没有得到服务器响应');
        }
        $data = $response->getData();
        return $data['media_id'];
    }

    /**
     * 删除永久素材
     * @param $mediaId
     * @return bool
     * @throws Exception
     * TODO 需要使用认证的公众号验证
     */

    public function deleteForeverMedia($mediaId){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_FOREVER_MEDIA_DELETE_URL . $this->accessToken)
            ->setMethod('post')
            ->setFormat(Client::FORMAT_JSON)
            ->setData(['media_id'=>$mediaId])
            ->send();
        if($response->isOk ==false){
            throw new Exception('网络问题没有得到服务器响应');
        }
        $data = $response->getData();

        if(isset($data['errcode']) && $data['errcode'] <> 0){
            throw new Exception($data['errmsg']);
        }
        return true;
    }

    /**
     * 获取永久素材总数的统计
     * @return mixed
     * @throws Exception
     * TODO 需要使用认证的公众号验证
     */
    public function foreverMediaTotal(){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_FOREVER_MEDIA_TOTAL_URL . $this->accessToken)
            ->setMethod('get')
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        if($response->isOk ==false){
            throw new Exception('网络问题没有得到服务器响应');
        }
        $data = $response->getData();

        if(isset($data['errcode']) && $data['errcode'] <> 0){
            throw new Exception($data['errmsg']);
        }
        return $data;
    }

    /**
     * 素材列表
     * @param string $type
     * @param int $offset
     * @param int $count
     * @return mixed
     * @throws Exception
     */
    public function foreverMediaList($type = 'image', $offset = 0, $count =20){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_FOREVER_MEDIA_LIST_URL . $this->accessToken)
            ->setMethod('post')
            ->setData([
                'type'=>$type,
                'offset'=>$offset,
                'count'=>$count
            ])
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        if($response->isOk == false){
            throw new Exception('网络问题, 没有得到服务器响应');
        }

        $data = $response->getData();
        if(isset($data['errcode']) && $data['errcode'] <> 0){
            throw new Exception($data['errmsg']);
        }
        return $data;
    }


    /**
     * 修改图文消息
     * @param $mediaId
     * @param $index
     * @param $article
     * @return bool
     * @throws Exception
     * TODO 需要使用认证的公众号验证
     */

    public function updateNews($mediaId,$index,$article){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_UPDATE_NEWS_URL . $this->accessToken)
            ->setMethod('post')
            ->setFormat(Client::FORMAT_JSON)
            ->setData(['media_id'=>$mediaId,'index'=>$index,'articles'=>$article])
            ->send();
        if($response->isOk ==false){
            throw new Exception('网络问题没有得到服务器响应');
        }
        $data = $response->getData();

        if($data['errcode'] == 0 ){
            return true;
        }else{
            throw new Exception($data['errmsg']);
        }
    }

}