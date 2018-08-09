<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/7
 * Time: 10:54
 */

namespace elilee\wx\payment;


use elilee\wx\helper\Xml;
use elilee\wx\core\Exception;
use yii\base\Component;

class Notify extends Component
{
    protected $notify;
    public $merchant;
    protected $data = false;

    public function getData(){
        if($this->data){
            return $this->data;
        }

        return $this->data = Xml::parse(file_get_contents('php://input'));
    }

    public function checkSign(){
        if($this->data == false){
            $this->getData();
        }

        $sign = $this->makeSign();
        if($sign != $this->data['sign']){
            throw new Exception('签名错误');
        }
        return true;
    }
    protected function makeSign(){
        $data = $this->data;
    }
}