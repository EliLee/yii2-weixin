wx
==

写在前边, 该扩展根据北哥yii2-wx课程,边学边写的，但不保证和北哥代码完全一致，如果想要深入学习可以到北哥网站（https://nai8.me/）学习相关课程。

之所以建立这个扩展主要是为了自用方便。

一个yii2de微信的sdk (自用)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist elilee/yii2-wx "*"
```

or add

```
"elilee/yii2-wx": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Setting
-------
在 params 中增加如下内容

```
'wx'=>[

        'app_id' => 'wxasdasf3slfmiem3mi2f',
        'secret' => '***************************',
        'token'  => 'elilee',

        'payment' => [
            'mch_id'        => '',//商户ID
            'key'           => '', //商户KEY
            'notify_url'    => '',//支付通知地址
            'cert_path'     => '',//证书
            'key_path'      => '',//证书
        ]
    ]
```

use
---
````
use elilee\wx\Application;

$config = Yii::$app->params['wx'];
$app = new Application(['conf'=>$config]);

$server=$app->driver('server');
$response = $server->server();

return $response;
````

function list
-------------

用户相关
支付相关
客服功能
红包相关
