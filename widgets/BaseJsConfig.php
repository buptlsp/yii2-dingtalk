<?php
namespace lspbupt\dingtalk\widgets;
use \Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\Dingtalk;

class BaseJsConfig extends Widget
{
    public $dingtalk = 'dingtalk';
    // 钉钉app内为"dd", 钉钉PC端为"DingTalkPC"
    public $jsObj = "dd";
    //是否需要认证,有些接口是不需要认证的
    public $needAuth = true;

    public $successJs;
    public $errorJs;
    public $jsApiList = [];
    // 0代表微应用，1代表服务窗
    public $type = 0;
    

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
            $this->errorJs = "function(error){alert(error.errorMessage);}"; 
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
        $jsObj = $this->jsObj;
        $js = $jsObj.".ready(".$this->successJs.");
        ".$jsObj.".error(".$this->errorJs.")";
        if($this->needAuth) {
            $arr = [
                'corpid' => $this->dingtalk->corpid,
                'agentId' => $this->dingtalk->agentid,
                'url' => $this->getUrl(),
                'type' => $this->type,
            ];
            $sign = $this->dingtalk->JsSign($arr);
            $js = $jsObj.".config({
                agentId: '".$arr['agentId']."', // 必填，微应用ID
                corpId: '".$arr['corpid']."',//必填，企业ID
                timeStamp: ".$arr['timestamp'].", // 必填，生成签名的时间戳
                nonceStr: '".$arr['noncestr']."', // 必填，生成签名的随机串
                signature: '".$sign."', // 必填，签名
                type: ".$arr['type'].", 
                jsApiList: ".json_encode($this->jsApiList)." // 必填，需要使用的jsapi列表
            });". $js;
        }
        $view->registerJs($js);
    }
}
