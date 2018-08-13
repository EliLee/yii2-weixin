<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/13
 * Time: 14:06
 */

namespace elilee\wx\menu;


use elilee\wx\accessToken\AccessToken;
use elilee\wx\core\Driver;
use elilee\wx\core\Exception;
use yii\httpclient\Client;

class Menu extends Driver
{
    private  $accessToken;

    const API_MENU_GET_URL = 'https://api.weixin.qq.com/cgi-bin/menu/get';
    const API_MENU_CREATE_URL = 'https://api.weixin.qq.com/cgi-bin/menu/create';

    public function init()
    {
        parent::init();
        $this->accessToken = (new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();
    }

    /**
     * 查看菜单
     * @return mixed
     * @throws Exception
     */
    public function ls(){
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_MENU_GET_URL.'?access_token='.$this->accessToken)
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        if($response->isOk ==false){
            throw new Exception('网络问题');
        }

        $data = $response->getData();
        if(isset($data['errcode']) && $data['errcode'] != 0){
            throw new Exception($data['errmsg'], $data['errcode']);
        }
        return $data;
    }

    /**
     * 创建菜单
     *  1、自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。
        2、一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。
     * $button=[
            [
                "type" => "view",
                "name" => "大厅",
                "url" => "http://xx.oo.com"

            ],
            [
                "name" => "有赏",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "玩家",
                        "url" => "http://xx.oo.com"
                    ],

                    [
                        "type" => "click",
                        "name" => "抢红包",
                        "key" => "qianghongbao"
                    ],
                ]
            ],
            [
                "name" => "有礼",
                "sub_button" => [

                    [
                        "type" => "view",
                        "name" => "开通",
                        "url" => "http://xx.oo.com"
                    ],
                    [
                        "type" => "click",
                        "name" => "商务合作",
                        "key" => "swhz"
                    ],
                ]

            ],
        ];
        $buttons = ['button'=>$button]
     * @param array $buttons
     * @return bool
     * @throws Exception
     */
    public function create($buttons=[]){
        $this ->httpClient->formatters = ['uncodeJson'=>'elilee\wx\helper\JsonFormatter'];

        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_MENU_CREATE_URL.'?access_token='.$this->accessToken)
            ->setMethod('post')
            ->setFormat('uncodeJson')
            ->setData($buttons)
            ->send();
        if($response->isOk ==false){
            throw new Exception('网络问题');
        }

        $data = $response->getData();
        Yii::info(var_export($data,1));
        if(isset($data['errcode']) && $data['errcode'] != 0){
            throw new Exception($data['errmsg'], $data['errcode']);
        }
        return true;
    }


}