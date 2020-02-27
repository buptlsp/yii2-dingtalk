<?php
namespace lspbupt\dingtalk\widgets;
use \Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\Dingtalk;
use lspbupt\dingtalk\assets\DingtalkPcAsset;

class JsapiPcConfig extends BaseJsConfig
{
    public $jsObj = "DingTalkPC";
    
    public function beforeRun()
    {
        $ua = Yii::$app->request->userAgent;
        //如果ua是钉钉Pc环境,该widget才会运行
        if(strpos($ua, 'DingTalk') == false || strpos($ua, 'AliApp') !== false) {
            return false;
        }
        $view = $this->getView();
        DingtalkPcAsset::register($view);
        return parent::beforeRun(); 
    }
}
