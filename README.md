wx
==
一个专注 yii2 de微信的sdk

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

        'app_id' => 'wxe1dfb09bf0d38ab0',
        'secret' => '336064c6e8bff6af12dd5921d0fc8541',
        'token'  => 'leelee',

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
