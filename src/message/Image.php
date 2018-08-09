<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/2
 * Time: 17:52
 */

namespace elilee\wx\message;


use elilee\wx\core\Driver;

class Image extends Driver
{
    public $type = 'image';
    public $props = [];
}