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
    public static $default=[
        'subscribe'=>'欢迎关注',
        'contact'=>'商务合作及广告合作请联系\n 13112341234 '
    ];
    public static $keyWordList =[
        [
            "key" => "111000",
            "type" => "text",
            "remsg" => "点击可查看<a href='#'>测试支付</a>",
            "start" => 1520092800,
            "end" => 1543593600
        ],
        [
            "key" => "123",
            "type" => "image",
            "remsg" => "/web/images/123.jpg",
            "start" => 1458543600,
            "end" => 1506827471
        ],
        [
            "key" => "5207",
            "type" => "news",
            "remsg" => [
                [
                    'title'=>'标题',
                    'description'=>'描述',
                    'picurl'=>'http://#/jpkj.jpg',
                    'url'=>'#',
                ],

            ],
            "start" => 1458543600,
            "end" => 1506827471
        ],

    ];
    public $type = 'text';
    public $props = [];

}