<?php
namespace lspbupt\dingtalk\actions;

use Closure;
use yii\base\Action;
use yii\di\Instance;
use yii\base\InvalidConfigException;

class BaseLoginAction extends Action
{
    public $dingapp;
    public $backUrl;
    public $returnCallback;
    public $loginCallback;
    
    public function init()
    {
        parent::init();
        $this->dingapp = Instance::ensure($this->dingapp);
        if(!$this->controller instanceof \yii\web\Controller) {
            throw new InvalidConfigException("该action只能在网页中使用，不支持命令行");
        }
    }
} 
