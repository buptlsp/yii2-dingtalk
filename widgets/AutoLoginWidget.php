<?php
namespace lspbupt\dingtalk\widgets;
use \Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\widgets\JsapiConfig;
use lspbupt\dingtalk\widgets\JsapiPcConfig;
use lspbupt\dingtalk\assets\AutoLoginAsset;

// 主要用于钉钉App或者钉钉PC端自动登录
class AutoLoginWidget extends BaseJsConfig
{
    // ajax登陆的接口，参见AjaxLoginAction
    public $ajaxUrl = "";
    public $successJs = "function(data){}";
    public $errorJs = "function(data){}";

    public function run()
    {
        $view = $this->getView();
        echo JsapiConfig::widget([
            'dingtalk' => $this->dingtalk,
            'needAuth' => true,
            'jsApiList' => ['runtime.permission.requestAuthCode'],
            'successJs' => 'function(){
                ddLogin("'.$this->ajaxUrl.'", "'.$this->dingtalk->corpid.'", '.$this->successJs.', '.$this->errorJs.');
            }',
        ]);
        echo JsapiPcConfig::widget([
            'dingtalk' => $this->dingtalk,
            'needAuth' => false,
            'jsApiList' => ['runtime.permission.requestAuthCode'],
            'successJs' => 'function(){
                ddPcLogin("'.$this->ajaxUrl.'", "'.$this->dingtalk->corpid.'", '.$this->successJs.', '.$this->errorJs.');
            }',
        ]);
        AutoLoginAsset::register($view);
    }
}

