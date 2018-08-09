<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/3
 * Time: 13:53
 */

namespace elilee\wx\helper;


class JsonFormatter extends \yii\httpclient\JsonFormatter
{
    public $encodeOptions = 256;
}