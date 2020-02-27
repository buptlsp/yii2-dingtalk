<?php
namespace lspbupt\dingtalk\widgets;
use \Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\Dingtalk;
use lspbupt\dingtalk\assets\DingtalkAsset;

class JsapiConfig extends BaseJsConfig
{
    public $jsObj = "dd";

    public function beforeRun()
    {
        $ua = Yii::$app->request->userAgent;
        //如果ua是钉钉环境,该widget才会运行
        if(strpos($ua, 'DingTalk') == false || strpos($ua, 'AliApp') == false) {
            return false;
        }
        $view = $this->getView();
        DingtalkAsset::register($view);
        return parent::beforeRun();
    }
}
