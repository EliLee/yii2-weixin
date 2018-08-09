<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/9
 * Time: 16:05
 */

namespace elilee\wx\message;


use elilee\wx\core\Driver;

class Transfer extends Driver
{
    public $type = 'transfer_customer_service';
    public $props = [];

}