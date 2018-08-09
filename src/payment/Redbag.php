<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/7
 * Time: 10:55
 */

namespace elilee\wx\payment;

use Yii;
use elilee\wx\core\Driver;
use elilee\wx\core\Exception;
use elilee\wx\helper\Util;

class Redbag extends Driver
{
    /**
     * 发送普通红包
     * 规则：
     *  1.发送频率限制------默认1800/min

        2.发送个数上限------按照默认1800/min算

        3.金额限制------默认红包金额为1-200元，如有需要，可前往商户平台进行设置和申请

        4.其他其他限制吗？------单个用户可领取红包上线为10个/天，如有需要，可前往商户平台进行设置和申请

        5.如果量上满足不了我们的需求，如何提高各个上限？------金额上限和用户当天领取次数上限可以在商户平台进行设置

        注意-红包金额大于200或者小于1元时，请求参数scene_id必传，参数说明见下文。
        注意2-根据监管要求，新申请商户号使用现金红包需要满足两个条件：1、入驻时间超过90天 2、连续正常交易30天。

        注意3-移动应用的appid无法使用红包接口。
     */
    const API_SEND_NORMAL_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    /**
     * 发送裂变红包
     */
    const API_SEND_GROUP_URL ='https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack';
    /**
     * 查询红包列表
     */
    const API_QUERY_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo';

    /**
     * 发送 普通红包或者裂变红包
     * 举个例子：
     *  裂变红包
     *      $params = [
     *          'mch_billno'=>'bill9090', //商户订单号
     *          'send_name'=>'家乐福',
     *          're_openid'=>'asdasd-sadasd1e1e21',
     *          'total_amount'=>300,
     *          'total_num'=>3,
     *          'wishing'=>'祝福你。。。。',
     *          'act_name'=>'春节促销',
     *          'remark'=>'1231231'
     *      ]
     * @param $params
     * @param string $type  group || normal
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function send($params,$type='normal'){
        $conf = [
            'nonce_str'=>Yii::$app->security->generateRandomString(32),
            'mch_id'=>$this->conf['payment']['mch_id'],
            'wxappid'=>$this->conf['app_id']
        ];
        if($type == 'group'){
            $conf['amt_type'] ='ALL_RAND';
        }else{
            $conf['client_ip'] = Yii::$app->request->userIP;
        }

        $params =array_merge($params,$conf);
        $params['sign'] = Util::makeSign($params,$this->conf['payment']['key']);

        $certs =[
            'SSLCERT'=>$this->conf['payment']['cert_path'],
            'SSLKEY'=>$this->conf['payment']['key_path'],
        ];

        $response = $this->httpClient->createRequest()
            ->setUrl($type == 'normal' ? self::API_SEND_NORMAL_URL : self::API_SEND_GROUP_URL)
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

    /**
     * 查询红包
     * @param $mchBillno
     * @return mixed
     * @throws Exception
     */
    public function query($mchBillno){
        $params = [
            'appid'=>$this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'mch_billno'=>$mchBillno,
            'bill_type'=>'MCHT',
            'nonce_str' => Yii::$app->security->generateRandomString(32)
        ];

        $params['sign'] =Util::makeSign($params,$this->conf['payment']['key']);
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