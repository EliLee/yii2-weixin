<?php
/**
 * 企业付款到零钱
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/7
 * Time: 10:54
 */

namespace elilee\wx\payment;

use Yii;
use elilee\wx\core\Driver;
use elilee\wx\core\Exception;
use yii\httpclient\Client;
use elilee\wx\helper\Util;

class Mch extends Driver
{
    const API_SEND_URL = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
    const API_QUERY_URL = "https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo";

    /**
     * 发送
     * $params = [
     *  'partner_trade_no'=>'xxx',
     *  'openid'=>'xxx',
     *  'amount'=>'xxx',
     *  'desc'=>'',
     *  'check_name'=>'NO_CHECK'
     * ]
     * @param array $params
     * @throws \yii\base\Exception
     */
    public function send($params=[]){
        $conf = [
            'mch_appid' => $this->conf['app_id'],
            'mchid'=>$this->conf['payment']['mch_id'],
            'spbill_create_ip'=>Yii::$app->request->userIP,
            'nonce_str'=>Yii::$app->security->generateRandomString(32)
        ];

        $params =array_merge($params, $conf);
        $params['sign'] = Util::makeSign($params,$this->conf['payment']['key']);

        $certs =[
            'SSLCERT'=>$this->conf['payment']['cert_path'],
            'SSLKEY'=>$this->conf['payment']['key_path'],
        ];

        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_SEND_URL)
            ->setMethod('post')
            ->setData($params)
            ->setFormat(Client::FORMAT_XML)
            ->setOptions([
                CURLOPT_SSLCERTTYPE=>'PEM',
                CURLOPT_SSLCERT=>$certs['SSLCERT'],
                CURLOPT_SSLKEYTYPE=>'PEM',
                CURLOPT_SSLKEY=>$certs['SSLKEY'],
            ])
            ->send();
        if($response->isOk == false){
            throw new Exception('无响应');
        }

        $result = $response->getData();

        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['return_code']);
        }

        if($result['result_code'] == 'FAIL'){
            throw new Exception($result['err_code']."#".$result['err_code_des']);
        }

        return $result;
    }


    public function query($partnerTradeNo){
        $params = [
            'appid'=>$this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'partner_trade_no'=>$partnerTradeNo,
            'nonce_str'=>Yii::$app->security->generateRandomString(32)
        ];
        $params['sign'] = Util::makeSign($params,$this->conf['payment']['key']);

        $certs =[
            'SSLCERT'=>$this->conf['payment']['cert_path'],
            'SSLKEY'=>$this->conf['payment']['key_path'],
        ];

        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_QUERY_URL)
            ->setMethod('post')
            ->setData($params)
            ->setFormat(Client::FORMAT_XML)
            ->setOptions([
                CURLOPT_SSLCERTTYPE=>'PEM',
                CURLOPT_SSLCERT=>$certs['SSLCERT'],
                CURLOPT_SSLKEYTYPE=>'PEM',
                CURLOPT_SSLKEY=>$certs['SSLKEY'],
            ])
            ->send();

        if($response->isOk == false){
            throw new Exception('无响应');
        }

        $result = $response->getData();

        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['return_code']);
        }

        if($result['result_code'] == 'FAIL'){
            throw new Exception($result['err_code']."#".$result['err_code_des']);
        }

        return $result;

    }

}