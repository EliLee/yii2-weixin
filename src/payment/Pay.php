<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/6
 * Time: 18:15
 */

namespace elilee\wx\payment;

use elilee\wx\helper\Xml;
use Yii;
use elilee\wx\core\Driver;
use elilee\wx\core\Exception;
use yii\httpclient\Client;
use elilee\wx\helper\Util;

class Pay extends Driver
{
    const QUERY_REFUND_TRANSACTION_ID='transaction_id';
    const QUERY_REFUND_OUT_TRADE_NO='out_trade_no';
    const QUERY_REFUND_OUT_REFUND_NO='out_refund_no';
    const QUERY_REFUND_REFUND_ID='refund_no';

    /**
     * 预支付订单接口地址
     */
    const API_PREPARE_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    /**
     * 查询订单
     */
    const API_QUERY_URL = 'https://api.mch.weixin.qq.com/pay/orderquery';

    /**
     * 关闭订单
     */
    const API_CLOSE_URL = 'https://api.mch.weixin.qq.com/pay/closeorder';

    private $prepare;

    protected function prepare($attributes=[]){
        if(empty($attributes['out_trade_no'])){
            throw new Exception('缺少统一支付接口必填参数 out_trade_no!');
        }elseif (empty($attributes['body'])){
            throw new Exception('缺少统一支付接口必填参数 body!');
        }elseif (empty($attributes['total_fee'])){
            throw new Exception('缺少统一支付接口必填参数 total_fee!');
        }elseif (empty($attributes['trade_type'])){
            throw new Exception('缺少统一支付接口必填参数 trade_type!');
        }

        if(empty($attributes['notify_url'])){
            throw new Exception('异步通知地址不能为空');
        }

        $attributes['appid'] = $this->conf['app_id'];
        $attributes['mch_id'] = $this->conf['payment']['mch_id'];
        $attributes['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $attributes['nonce_str'] = Yii::$app->security->generateRandomString(32);
        $attributes['sign'] = $this->makeSign($attributes);

        $xml = $this->toXml($attributes);
        $response = $this->httpClient->createRequest()
            ->setUrl(self::API_PREPAPE_URL)
            ->setMethod('post')
            ->setOptions([
                CURLOPT_POSTFIELDS=>$xml
            ])
            ->send();
        $response->setFormat(Client::FORMAT_XML);
        return $this->prepare = $response->getData();


    }
    public function checkSign($vals){
        $sign = $this->makeSign($vals);
        return $sign == $vals['sign'];
    }
    
    private function toXml($vals){
        if(!is_array($vals)
            || count($vals) <= 0){
            throw new Exception("数组数据异常");
        }

        $xml = "<xml>";
        foreach ($vals as $key=>$val){
            if(is_numeric($val)){
                $xml .= "<".$key.">".$val."</".$key.">";
            }else{
                $xml .="<".$key."><!CDATA[".$val."]]></".$key.">";
            }
        }

        $xml .="</xml>";
        return $xml;
    }

    /**
     * 原始扫码登录
     * @param array $attributes 原始扫码所需参数
     * @return mixed
     * @throws Exception
     */
    public function native($attributes=[]){
        $attributes['trade_type'] ='NATIVE';
        $result = $this->prepare($attributes);
        return $result;
    }



    public function nativeDefinedQrcode($productId){
        $params = [
            'appid'=>$this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'time_stamp'=>time(),
            'nonce_str'=>Yii::$app->security->generateRandomString(32),
            'product_id'=>$productId
        ];
        $sign = Util::makeSign($params,$this->conf['payment']['key']);

        $codeUrl = "weixin://wxpay/bizpayurl?appid={$params['appid']}&mch_id={$params['mch_id']}&nonce_str={$params['nonce_str']}&product_id={$productId}&time_stamp={$params['time_stamp']}&sign={$sign}";

        return urlencode($codeUrl);
    }

    /**
     * 原生预支付订单
     * @param $attributes
     * @return string
     * @throws Exception
     * @throws \yii\base\Exception
     */

    public function nativeDefinedResponse($attributes){
        $attributes['trade_type'] = 'NATIVE';
        $prepare = $this -> prepare($attributes);

        $responseParams = [
            'return_code'=>'SUCCESS',
            'appid'=>$this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'nonce_str'=>Yii::$app->security->generateRandomString(32),
            'prepay_id'=>$prepare['prepay_id'],
            'result_code'=>'SUCCESS',
        ];

        $responseParams['sign'] =$this->makeSign($responseParams);

        return Xml::build($responseParams);

    }
    /**
     * JSSDK支付
     * @param array $attributes JSSDK 支付需要的参数
     * @return mixed
     * @throws Exception
     */
    public function js($attributes=[]){
        $attributes['trade_type'] = "JSAPI";
        $result =$this->prepare($attributes);
        return $result;
    }

    public function configForPayment($parepayId){
        $params = [
            'appId'=>$this->conf['app_id'],
            'timeStamp'=>strval(time()),
            'nonceStr'=>uniqid(),
            'package'=>"prepay_id=$parepayId",
            'signType'=>'MD5'
        ];
        $params['paySign'] =Util::makeSign($params,$this->conf['payment']['key']);
        return $params;
    }

    protected function getNotify(){
        return (new Notify(['merchant' => $this->conf['payment']]));
    }

    public function handleNotify(callable $callback){
        $notify = $this->getNotify();

        if(!$notify->checkSign()){
            throw new Exception('签名错误');
        }

        $notify = $notify->getData();
        $isSuccess = $notify['result_code'] === 'SUCCESS';

        $handleResult =call_user_func_array($callback, [$notify,$isSuccess]);

        if(is_bool($handleResult) && $handleResult){
            $response =[
                'return_code'=>'SUCCESS',
                'return_msg'=>'OK',
            ];
        } else{
            $response = [
                'return_code'=>'FAIL',
                'return_msg' => $handleResult,
            ];

        }

        return Xml::build($response);
    }

    /**
     * 获取订单信息
     * @param $outTradeNo 商户订单号/交易号
     * @param bool $isTransaction 是否是系统的
     * @return mixed
     * @throws Exception
     */
    public function query($outTradeNo, $isTransaction =false){
        $params = [
            'appid' => $this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'nonce_str' => Yii::$app->security->generateRandomString(32),
            'sign_type'=>'MD5'
        ];
        if($isTransaction == true){
            //微信返回的交易号码
            $params['transaction_id'] = $outTradeNo;

        }else{
            //商户订单号
            $params['out_trade_no'] = $outTradeNo;
        }
        $params['sign'] =Util::makeSign($params,$this->conf['payment']['key']);
        $response = $this->httpClient->createRequest()
                    ->setUrl(self::API_QUERY_URL)
                    ->setMethod('post')
                    ->setData($params)
                    ->setFormat(Client::FORMAT_XML)
                    ->send();
        if($response->isOk == false){
            throw new Exception('无响应');
        }
        return $response->getData();
    }

    /**
     * 关闭订单
     * 最短5分钟的订单才可以关闭， 关闭不存在订单也会返回成功
     * @param $outTradeNo
     * @return mixed
     * @throws Exception
     */
    public function close($outTradeNo){
        $params = [
            'appid'=>$this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'out_trade_no'=>$outTradeNo,
            'nonce_str'=>Yii::$app->security->generateRandomString(32)
        ];
        $params['sign']=Util::makeSign($params,$this->conf['payment']['key']);

        $response = $this->httpClient->createrequest()
            ->setUrl(self::API_CLOSE_URL)
            ->setMethod('post')
            ->setData($params)
            ->setFormat(Client::FORMAT_XML)
            ->send();
        if($response->isOk == false){
            throw new Exception('无响应');
        }

        $result = $response ->getData();

        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['return_msg']);
        }
        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['err_code']."#".$result['err_code_des']);
        }
        return $result;
    }

    /**
     * 退款操作
     * @param $outTradeNo string 商户订单号 （$isTransactionId false）/ 微信订单号 （$isTransactionId true）
     * @param bool $isTransactionId
     * @param $outRefundNo
     * @param $totalFee
     * @param $refundFee
     * @param array $extra
     * @throws Exception
     */

    public function refund($outTradeNo,$isTransactionId=false,$outRefundNo,$totalFee,$refundFee,$extra=[]){
        $params = [
            'appid' => $this->conf['app_id'],
            'mch_id' => $this->conf['payment']['mch_id'],
            'nonce_str'=> Yii::$app->security->generateRandomString(32),
            'out_refund_no' =>$outRefundNo,
            'total_fee'=>$totalFee,
            'refund_fee'=>$refundFee,
        ];
        if($isTransactionId == true){
            $params['transaction_id'] = $outTradeNo;
        }else{
            $params['out_refund_no'] = $outTradeNo;
        }
        if($extra){
            $params = array_merge($params, $extra);
        }
        $params['sign'] = Util::makeSign($params,$this->conf['payment']['key']);

        $certs = [
            'SSLCERT' => $this->conf['payment']['cert_path'],
            'SSLKEY'  => $this->conf['payment']['key_pay']
        ];

        $response = $this->httpClient->createRequest()
                ->setUrl(self::API_REFUND_URL)
                ->setMethod('post')
                ->setData($params)
                ->setOptions([
                    CURLOPT_SSLCERTTYPE=>'PEM',
                    CURLOPT_SSLCERT=>$certs['SSLCERT'],
                    CURLOPT_SSLKEYTYPE=>'PEM',
                    CURLOPT_SSLKEY=>$certs['SSLKEY'],
                ])
                ->setFormat(Client::FORMAT_XML)
                ->send();
        if($response->isOk == false){
            throw new Exception('无响应');
        }
        $result = $response->getData();

        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['return_msg']);
        }
        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['err_code']."#".$result['err_code_des']);
        }

        return $result;
    }

    public function queryRefund($number, $type =self::QUERY_REFUND_OUT_TRADE_NO){
        $params = [
            'appid'=>$this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'nonce_str'=>Yii::$app->security->generateRandomString(32)
        ];

        switch($type){
            case self::QUERY_REFUND_OUT_TRADE_NO:
                $params['out_trade_no'] =$number;
            case self::QUERY_REFUND_TRANSACTION_ID:
                $params['transaction_id'] =$number;
            case self::QUERY_REFUND_OUT_REFUND_NO:
                $params['out_refund_no'] =$number;
            case self::QUERY_REFUND_REFUND_NO:
                $params['refund_id'] =$number;
                break;
        }
        $params['sign'] = Util::makeSign($params,$this->conf['payment']['key']);
        $response = $this->post(self::API_REFUND_QUERY_URL,$params)->setFormat(Client::FORMAT_XML)->send();
        if($response->isOk == false){
            throw new Exception(self::ERROR_NO_RESPONSE);
        }
        $response->setFormat(Client::FORMAT_XML);
        $result = $response->getData();
        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['return_msg']);
        }
        if($result['result_code'] == 'FAIL'){
            throw new Exception($result['err_code']."#".$result['err_code_des']);
        }
        return $result;



    }

    /**
     * 退款通知
     * @param callable $callback
     * @return string
     */
    public function handleRefundNotify(callable $callback){
        $notify = (new RefundNotify(['merchant'=>$this->conf['payment']]))->getData();
        $isSuccess =$notify['return_code'] === 'SUCCESS';

        $handleResult = call_user_func_array($callback, [$notify, $isSuccess]);
        if(is_bool($handleResult) && $handleResult){
            $response = [
                'return_code' =>'SUCCESS',
                'return_msg'  =>'OK'
            ];
        }else{
            $response = [
                'return_code'=>'FAIL',
                'return_msg'=>$handleResult
            ];
        }
        return Xml::build($response);
    }

    /**
     * 对账单
     * 使用方法：
     *          $response = $pay->bill('20180909',Pay::TYPE_BILL_ALL);
     *          header('Content-Desposition: attachment; filename="20180909.csv"');
     *          return $response
     * @param $date
     * @param $type
     * @return mixed
     * @throws Exception
     */
    public function bill($date,$type = self::TYPE_BILL_ALL){
        $params =[
            'appid'=>$this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'nonce_str'=>Yii::$app->security->generateRandomString(32),
            'bill_date'=>$date,
            'bill_type'=>$type
        ];
        $params['sign'] = Util::makeSign($params,$this->conf['payment']['key']);
        $response=$this->httpClient->createRequest()
            ->setMethod('post')
            ->setUrl(self::API_DOWNLOAD_BILL_URL)
            ->setFormat(Client::FORMAT_XML)
            ->send();
        if($response->isOk ==false){
            throw new Exception('无响应');
        }
        return $response->getContent();
    }

    /**
     * 支付转换短连接
     * @param $longUrl
     * @return mixed
     * @throws Exception
     */
    public function url2short($longUrl){
        $params =[
            'appid'=>$this->conf['app_id'],
            'mch_id'=>$this->conf['payment']['mch_id'],
            'nonce_str'=>Yii::$app->security->generateRandomString(32),
            'long_url'=>$longUrl
        ];
        $params['sign'] = Util::makeSign($params,$this->conf['payment']['key']);
        $response = $this->httpClient->createRequest()
                ->setMethod('post')
                ->setUrl(self::API_SHORT_URL_URL)
                ->setData($params)
                ->setFormat(Client::FORMAT_XML)
                ->send();
        if($response->isOk == false){
            throw new Exception('无响应');
        }
        $response->setFormat(Client::FORMAT_XML);
        $result = $response->getData();
        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['return_msg']);
        }
        if($result['return_code'] == 'FAIL'){
            throw new Exception($result['err_code']."#".$result['err_code_des']);
        }
        return $result['short_url'];
    }
}