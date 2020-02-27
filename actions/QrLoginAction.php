<?php
namespace lspbupt\dingtalk\actions;

use Closure;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\DingApp;
use lspbupt\dingtalk\actions\BaseLoginAction;

/** 主要用于扫码登录
 *  用户扫码后，通过js的handleMsg,应该跳转至本action处理，本action通过code获取相关信息后进行登陆操作
 * */
class QrLoginAction extends BaseLoginAction
{
    public $dingapp = "dingapp";
    public $backUrl = "/site/login";
    
    public function init()
    {
        parent::init();
        if(!$this->dingapp instanceof DingApp) {
            throw new InvalidConfigException("钉钉扫码app配置错误");
        }
        if(empty($this->returnCallback)) {
            $this->returnCallback = function($msg, $url){
                if($msg) {
                    Yii::$app->session->setFlash('error', $msg);
                }
                return $this->controller->redirect($url);
            };
        }
        //判断returnCallback 与loginCallback是否为闭包
        if (!($this->loginCallback instanceof Closure) || !($this->returnCallback instanceof Closure)) {
            throw new InvalidConfigException("callback必须为闭包");
        }
    }


    public function run()
    {
        $code = Yii::$app->request->get('code', '');
        $getArr = Yii::$app->request->get('state', '');
        $backUrl = $this->backUrl;
        !empty($getArr) && $backUrl .= '?'.$getArr;
        if (empty($code)) {
            return call_user_func($this->returnCallback, "", $backUrl);
        }
        $data = $this->dingapp->setPostJson()->httpExec('/sns/getuserinfo_bycode', [
            'tmp_auth_code' => $code,
        ]);
        if ($data['errcode'] != 0) {
            return call_user_func($this->returnCallback, $data['errmsg'], $backUrl);
        }
        $ret = call_user_func($this->loginCallback, $data);
        return call_user_func($this->returnCallback, $ret, $backUrl);
    }
} 
