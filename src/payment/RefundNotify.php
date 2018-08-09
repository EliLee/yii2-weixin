<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/8
 * Time: 10:17
 */

namespace elilee\wx\payment;


use elilee\wx\helper\Xml;
use yii\base\Component;

class RefundNotify extends Component
{
    public $merchant;
    private $data;

    public function getData(){
        if($this->data){
            return $this->data;
        }

        $data = Xml::parse(file_get_contents('php://input'));
        $data['req_info'] = $this->decodeInfo($data['req_info']);
        return $this->data=$data;
    }

    protected function decodeInfo($data){
        $decode64 = base64_decode($data,true);
        $key =md5($this->merchant['key']);
        $decrypted = openssl_decrypt($decode64,'aes-256-ecb', $key,OPENSSL_RAW_DATA);
        return Xml::parse($decrypted);
    }

}