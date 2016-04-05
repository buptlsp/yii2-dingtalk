<?php
namespace lspbupt\dingtalk;
use \Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\Dingtalk;
use lspbupt\dingtalk\DingtalkAsset;
class JsapiConfig extends Widget
{
    public $dingtalk = 'dingtalk';
    public $successJs;
    public $errorJs;
    public $jsApiList = [];

    public function init()
    {
        if (is_string($this->dingtalk)) {
            $this->dingtalk = Yii::$app->get($this->dingtalk);
        } elseif (is_array($this->dingtalk)) {
            if (!isset($this->dingtalk['class'])) {
                $this->dingtalk['class'] = Dingtalk::className();
            }
            $this->dingtalk = Yii::createObject($this->dingtalk);
        }
        if (!$this->dingtalk instanceof Dingtalk) {
            throw new InvalidConfigException("钉钉配置错误");
        }
        if (empty($this->errorJs)) {
            $this->errorJs = "function(error){alert(error.message);}"; 
        }
    }

    public function getUrl()
    {
        $url = "http";
        if (!empty($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {
            $url .= "s";
        }
        $url .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $url;  
    }

    public function run()
    {
        $view = $this->getView();
        DingtalkAsset::register($view);
        $arr = [
            'corpid' => $this->dingtalk->corpid,
            'agentId' => $this->dingtalk->agentid,
            'url' => $this->getUrl(),
        ];
        $sign = $this->dingtalk->JsSign($arr);
        $js ="dd.config({
             agentId: '".$arr['agentId']."', // 必填，微应用ID
             corpId: '".$arr['corpid']."',//必填，企业ID
             timeStamp: ".$arr['timestamp'].", // 必填，生成签名的时间戳
             nonceStr: '".$arr['noncestr']."', // 必填，生成签名的随机串
             signature: '".$sign."', // 必填，签名
             jsApiList: ".json_encode($this->jsApiList)." // 必填，需要使用的jsapi列表
        });
        dd.ready(".$this->successJs.");
        dd.error(".$this->errorJs.");";
        $view->registerJs($js);
    }
}
