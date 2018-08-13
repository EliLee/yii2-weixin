<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2018/8/1
 * Time: 10:25
 */

namespace elilee\wx;

use elilee\wx\js\Js;
use elilee\wx\menu\Menu;
use elilee\wx\oauth\OAuth;
use elilee\wx\qrcode\Shorturl;
use elilee\wx\resource\Resource;
use elilee\wx\user\Remark;
use elilee\wx\user\Tag;
use elilee\wx\user\User;
use elilee\wx\kf\Kf;
use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use elilee\wx\qrcode\Qrcode;
use elilee\wx\accessToken\AccessToken;
use elilee\wx\core\Base;
use elilee\wx\server\Server;

class Application extends Component
{
    public $conf;
    public $httpClient;
    public $classMap = [
        'base'=> Base::class,
        'qrcode'=> Qrcode::class,
        'shortUrl'=>Shorturl::class,
        'accessToken'=>AccessToken::class,
        'server'=>Server::class,
        'userInfo'=>User::class,
        'remark'=>Remark::class,
        'tag'=>Tag::class,
        'resource'=>Resource::class,
        'oauth'=>OAuth::class,
        'js'=>Js::class,
        'kf'=>Kf::class,
        'menu'=>Menu::class
    ];

    public function init()
    {
        parent::init();

        $this->httpClient = new Client();
    }

    public function driver($api, $extra=[])
    {

        $config=[
            'conf' => $this->conf,
            'httpClient'=>$this->httpClient,
            'extra'=>$extra
        ];
        $config['class']=$this->classMap[$api];

        return Yii::createObject($config);
    }
}