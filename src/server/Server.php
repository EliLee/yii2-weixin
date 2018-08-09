<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/2
 * Time: 12:02
 */

namespace elilee\wx\server;

use elilee\wx\helper\Xml;
use elilee\wx\message\Text;
use Yii;
use elilee\wx\core\Driver;
use elilee\wx\core\Exception;

class Server extends Driver
{
    protected $messageHandler;
    /**
     * 发送响应
     */
    public function server(){
        $this ->validate();
        if($echoStr = Yii::$app->request->get('echostr')){
            Yii::$app->response->content=$echoStr;
            Yii::$app->response->send();
            return true;
        }
        $result = $this->handleRequest();
        Yii::info(var_export($result,1));
        $response = $this->buildResponse($result['to'],$result['from'],$result['response']);

        Yii::$app->response->content =$response;
        Yii::$app->response->send();

    }
    /**
     * 验证签名
     */
    protected function validate(){
        $token =$this->conf['token'];
        $params = [
            $token,
            Yii::$app->request->get('timestamp'),
            Yii::$app->request->get('nonce'),
        ];
        if(Yii::$app->request->get('signature') !== $this->signature($params)){
            throw new Exception('无效签名',400);
        }
    }
    /**
     * 生成签名
     */
    protected function signature($params){
        sort($params,SORT_STRING);
        return sha1(implode($params));
    }

    protected function handleRequest(){
        $message =$this->getMessage();
        Yii::info(var_export($message,1));
        $response = $this->handleMessage($message);
        Yii::info(var_export($response,1));
        return [
            'to'=>$message['FromUserName'],
            'from'=>$message['ToUserName'],
            'response'=>$response
        ];
    }

    protected function getMessage(){
        $message =$this -> parseMessageInRequest(file_get_contents('php://input'));
        Yii::info('message'.var_export($message,1));
        return $message;
    }
    protected function parseMessageInRequest($content=null){
        $message =Xml::parse($content);
        return $message;
    }
    protected function handleMessage($message){
        $handler = $this->messageHandler;
        if(!is_callable($handler)){
            return false;
        }
        $type = $message['MsgType'];
        $response = null;
        if($type){
            $response =call_user_func_array($handler,[$message]);
        }
        return $response;
    }
    public function setMessageHandler($callback){
        if(!is_callable($callback)){
            throw new Exception('setMessageHandler error');
        }
        $this->messageHandler =$callback;
        return $this;
    }
    protected function buildResponse($to,$from,$message){
        if(empty($message) || 'success'===$message){
            return 'success';
        }

        //
        if(is_string($message) || is_numeric($message)){
            //组件传值即是赋值  因为Text 最终继承Component
            $message = new Text(['props'=>['Content'=>$message]]);
        }

        $response =$this->buildReply($to,$from,$message);
        return $response;
    }

    protected function buildReply($to,$from,$message){
        $base = [
            'ToUserName'=>$to,
            'FromUserName'=>$from,
            'CreateTime'=>time(),
            'MsgType'=>$message->type,
        ];
        return Xml::build(array_merge($base,$message->props));
    }

}