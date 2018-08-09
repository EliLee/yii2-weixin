<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/2
 * Time: 15:11
 */

namespace elilee\wx\message;


use elilee\wx\core\Driver;

class Text extends Driver
{
    public $type = 'text';
    public $props = [];

}