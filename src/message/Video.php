<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/2
 * Time: 17:54
 */

namespace elilee\wx\message;


use elilee\wx\core\Driver;

class Video extends Driver
{
    public $type = 'video';
    public $props = []; //MediaId
}