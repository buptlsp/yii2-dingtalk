<?php
namespace lspbupt\dingtalk\widgets;
use \Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\Dingtalk;
use lspbupt\dingtalk\DingtalkPcAsset;
class JsapiPcConfig extends Widget
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
        $request = \Yii::$app->request;
        $url = $request->hostInfo.urldecode($request->getUrl());
        return $url;  
    }

    public function run()
    {
        $view = $this->getView();
        DingtalkPCAsset::register($view);
        $arr = [
            'corpid' => $this->dingtalk->corpid,
            'agentId' => $this->dingtalk->agentid,
            'url' => $this->getUrl(),
        ];
        $sign = $this->dingtalk->JsSign($arr);
        $js ="DingTalkPC.config({
             agentId: '".$arr['agentId']."', // 必填，微应用ID
             corpId: '".$arr['corpid']."',//必填，企业ID
             timeStamp: ".$arr['timestamp'].", // 必填，生成签名的时间戳
             nonceStr: '".$arr['noncestr']."', // 必填，生成签名的随机串
             signature: '".$sign."', // 必填，签名
             jsApiList: ".json_encode($this->jsApiList)." // 必填，需要使用的jsapi列表
        });
        DingTalkPC.ready(".$this->successJs.");
        DingTalkPC.error(".$this->errorJs.");";
        $view->registerJs($js);
    }
}
