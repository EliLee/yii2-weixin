<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/8
 * Time: 15:23
 */

namespace elilee\wx\core;


class Exception extends \yii\base\Exception
{
    public function getName()
    {
        return 'yii2-wx-exception';
    }
}