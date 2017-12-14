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
php composer.phar require --prefer-dist "lspbupt/yii2-dingtalk" "1.1.1"
```

或加入

```
"lspbupt/yii2-dingtalk": "1.1.1"
```

到你的`composer.json`文件中的require段。

使用
-----

**钉钉配置** 

一旦你安装了这个插件，你就可以直接在yii2的配置文件中加入如下的代码：


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

**后端接口访问**

在配置好之后，你可以这么访问它：
```php
//在代码中实现了少量的接口，后续会不断补充，示例如：

// 下述代码会获取当前企业的部门列表
$data = Yii::$app->dingtalk->getDepartmentList();

//下述代码会给用户id为1的用户发一条测试的钉钉消息
$data = Yii::$app->dingtalk->sendTextMsg(1, "", "测试一下企业消息");
```

**后端接口返回**

本接口将钉钉的返回进行了包装，无论任何情况（如网络超时，接口挂掉等），返回的结果均为json格式，且一定拥有errcode和errmsg字段，因此，使用方可以简单地根据errcode来判断接口的返回。

```javascript
{
    "errcode":0,     // 0代表正确，非0代表失败
    "errmsg":"errinfo",  // 具体的错误信息
    //... ,其它钉钉接口返回的额外信息  
}
```

**其它接口调用**

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

**[app端Js调用][1]**

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

**[PC端Js调用][2]**

  使用方式与上述app端一致，调用JsapiPcConfig即可,如下依旧以免登作为示例：
  
  ```php
    echo \lspbupt\dingtalk\widgets\JsapiPcConfig::widget([
        'jsApiList' => ["runtime.permission.requestAuthCode"],
        'successJs' => 'function(){
            //参见上节
        }',
    ]);
  ```

**[扫码登录][3]**

扫码登录不是使用dingtalk的一套规则，而是使用app的一套规则，首先去钉钉后台添加app，并配置好redirectUrl。然后在config中添加如下的配置：

```php
return [
    'components' => [
        'dingapp' => [
             'class' => '\lspbupt\dingtalk\DingApp',
             'appid' => '',     // appid
             'appsecret' => '',  // appsecret
             'redirectUrl' => "", //登录成功后跳回的地址
         ],
        // ....
    ],
];
```
然后在页面上按钉钉的规则，引入js，并展示二维码,当用户扫描时，回跳转到你的url,你在action中可以这样获取用户信息

```php
$ret = Yii::$app->dingapp->getUserInfoByTmpCode($code, $userInfo);
if($ret) {
    //代表不成功，$ret代表错误信息
}
// $userInfo中有用户的信息，可以根据$userInfo来获取用户，并执行登录操作
```

**[消息回调][4]**

正常情况下，钉钉会有一些回调，这些回调需要在action中解密，过程很麻烦，本代码提供了一个便利的方式来使用。
在config中加入如下的配置：

```php
return [
    'components' => [
        'dingcrypt' => [
            'class' => '\lspbupt\dingtalk\helpers\CryptHelper',
            'aesKey' => '', // aeskey
            'token' => '',  //加密的token
        ],
        // 其它配置 
    ],
]
```

在注册成功的回调的controller中加入如下的代码即可：

```php
public class xxController extends Controller
{
    public function behaviors()
    {
        return [
            'dingcallback' => [
                'class' => \lspbupt\dingtalk\filters\CallbackFilter::className(),
                'actions' => ['index'], //你需要钉钉处理解密的请求
            ],
        ],
    }
    
    public function xxAction()
    {
        //获取钉钉传过来的数据进行处理
        $postArr = Yii::$app->request->post();
    }
}
```
如上所示，你不需要关心钉钉是如何加解密的，也不需要关心他传过来的数据格式，系统会自动解密并将它传过来的xml转换成php的arr，直接在post()中取即可。



如有任何问题，欢迎联系我（lspbupt@sina.com）。祝使用愉快!


  [1]: https://open-doc.dingtalk.com/docs/doc.htm?spm=0.0.0.0.RembXi&treeId=171&articleId=104906&docType=1
  [2]: https://open-doc.dingtalk.com/docs/doc.htm?spm=0.0.0.0.RembXi&treeId=176&articleId=106255&docType=1
  [3]: https://open-doc.dingtalk.com/docs/doc.htm?spm=a219a.7629140.0.0.i2zeN5&treeId=168&articleId=104882&docType=1
  [4]: https://open-doc.dingtalk.com/docs/doc.htm?spm=a219a.7629140.0.0.eOiJu9&treeId=385&articleId=104975&docType=1
