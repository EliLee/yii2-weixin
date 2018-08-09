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

Once the extension is installed, simply use it in your code by  :

```php
<?= \elilee\wx\AutoloadExample::widget(); ?>```