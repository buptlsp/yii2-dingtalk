# yii2-dingtalk
========================

yii2框架的钉钉API接口  

本框架提供了对钉钉API接口的封装，具体的API页面访问[钉钉API接口](http://ddtalk.github.io/dingTalkDoc/)  

For license information check the [LICENSE](LICENSE.md)-file.  
在此处可以查看本扩展的[许可](LICENSE.md)  


安装
------------

推荐的方式是通过composer 进行下载安装[composer](http://getcomposer.org/download/)。  

在命令行执行  
```
php composer.phar require --prefer-dist "lspbupt/yii2-dingtalk" "*"
```

或加入  

```
"lspbupt/yii2-dingtalk": "*"
```

到你的`composer.json`文件中的require-dev段。  

使用
-----

一旦你安装了这个插件，你就可以直接在配置文件中加入如下的代码：  


```php
return [
    'components' => [
        'dingtalk' => [
             'class' => '\lspbupt\dingtalk\Dingtalk',
             'agentid' => '', //您的应用的agentid 
             'corpid' => '',  //您的企业corpid
             'corpsecret' => '', //您的企业的corpsecret
        ],
        // .... 
    ],   
];
```

在配置好之后，你可以这么访问它：   
```php
//在代码中实现了少量的接口，后续会不断补充，示例如：  

// 下述代码会获取当前企业的部门列表
$data = Yii::$app->dingtalk->getDepartmentList();

//下述代码会给用户id为1的用户发一条测试的钉钉消息  
$data = Yii::$app->dingtalk->sendTextMsg(1, "", "测试一下企业消息");
```

由于钉钉的接口众多，因此想一一实现也是一件特别繁琐的事情，但是本扩展依旧提供了一个方便的方法来调用它：  
```php
//通过GET方法获取dingding的企业部门列表
$data = Yii::$app->dingtalk->setGet()->httpExec("/department/list", []);
//通过POST JOSN来发送钉钉消息
$data = Yii::$app->dingtalk->setPostJson()->httpExec("/message/send", [
    'touser' => $user,
    'toparty' => $toparty,
    'agentid' => Yii::$app->dingtalk->agentid,
    'msgtype' => 'text',
    'text' => [
        'content' => '测试一下企业消息'
    ],
]);
```

本插件依旧提供了JSAPI的一些封装,本例将会以app的免登的JS来举例子，在某页面上需要免登时，你只需要在页面上添加如下的代码：  
```php 
echo \lspbupt\dingtalk\JsapiConfig::widget([
    'jsApiList' => ["runtime.permission.requestAuthCode"], //本页面需要使用的jsapi,本例中为免登服务
    'successJs' => 'function(){ //jsapi配置好后执行的JS回调，我们可以在此处开始写执行的代码
         dd.runtime.permission.requestAuthCode({
             corpId: "'.\Yii::$app->dingtalk->corpid.'",
             onSuccess: function(result) {
                 $.ajax({
                     url: "", //此处填上根据code登录的url
                     data: {
                         code: result.code
                     },
                     success: function(data){  //处理成功请求
                     },
                 });
             },
             onFail : function(err) {
                 //alert(err.errmsg);
             }
         });
    }',
    //'errorJs' => 'function(){}', //错误时的JS,默认会输出错误的信息
]);
```
按钉钉的说明做好使用code登录的后台接口，即可实现页面免登。  


如有任何问题，欢迎联系我。祝使用愉快!  
