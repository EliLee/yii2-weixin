<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/2
 * Time: 17:53
 */

namespace elilee\wx\message;


use elilee\wx\core\Driver;

class News extends Driver
{
    public $type = 'news';
    public $props = [];
}