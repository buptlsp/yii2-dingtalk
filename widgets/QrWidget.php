<?php
namespace lspbupt\dingtalk\widgets;
use \Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\DingApp;
use lspbupt\dingtalk\assets\DingQrAsset;
use yii\helpers\Html;
//主要用于网页扫码登录
class QrWidget extends Widget
{
    public $dingapp = 'dingapp';
    public $id = "login_container";
    public $href = "";
    public $width = "320px";
    public $height = "320px";
    // 为了防止与其它js插件的message冲突，只接收来自origin的消息
    public $origin = "https://login.dingtalk.com";

    public function init()
    {
        if (is_string($this->dingapp)) {
            $this->dingapp = Yii::$app->get($this->dingapp);
        } elseif (is_array($this->dingapp)) {
            if (!isset($this->dingapp['class'])) {
                $this->dingapp['class'] = DingApp::className();
            }
            $this->dingapp = Yii::createObject($this->dingapp);
        }
        if (!$this->dingapp instanceof DingApp) {
            throw new InvalidConfigException("钉钉配置错误");
        }
        parent::init();
    }
   
    //获取跳转至钉钉的url
    public function getGoto()
    {
        return urlencode('https://oapi.dingtalk.com/connect/oauth2/sns_authorize?appid='.$this->dingapp->appid.          '&response_type=code&scope=snsapi_login&state=STATE&redirect_uri='.$this->dingapp->redirectUrl);
    }
 
    public function run()
    {
        echo Html::tag('span', '', [
            'id' => $this->id,
        ]);
        $view = $this->getView();
        DingQrAsset::register($view);
        $js = '
            var obj = DDLogin({
                id: "'.$this->id.'",
                goto : "'.$this->getGoto().'",
                href: "'.$this->href.'",
                width:"'.$this->width.'",
                height:"'.$this->height.'",
            });
            var handleMessage = function (event) {
                var origin = event.origin;
                if( origin !== "'.$this->origin.'" ) {
                    return false;
                }
                var loginTmpCode = event.data; //拿到loginTmpCode后就可以在这里构造跳转链接进行跳转了
                var oldUrl = location.href;
                var paramStr = "";
                if(oldUrl.indexOf("?") > -1){
                    paramStr = encodeURIComponent(oldUrl.substring(oldUrl.indexOf("?")+1, oldUrl.length));
                }
                var url = "https://oapi.dingtalk.com/connect/oauth2/sns_authorize?appid='.$this->dingapp->appid.'&response_type=code&scope=snsapi_login&state=" + encodeURIComponent(paramStr) +"&redirect_uri='.$this->dingapp->redirectUrl.'?&loginTmpCode=" + loginTmpCode;
                window.location.href=url
            };
            if (typeof window.addEventListener != "undefined") {
                window.addEventListener("message", handleMessage, false);
            } else if (typeof window.attachEvent != "undefined") {
                window.attachEvent("onmessage", handleMessage);
            }
        ';
        $view->registerJs($js);
    }
}
