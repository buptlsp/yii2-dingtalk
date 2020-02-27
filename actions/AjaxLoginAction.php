<?php
namespace lspbupt\dingtalk\actions;

use Closure;
use yii\base\Action;
use yii\di\Instance;
use yii\base\InvalidConfigException;
use lspbupt\dingtalk\Dingtalk;
use lspbupt\dingtalk\actions\BaseLoginAction;
use Yii;

/** 主要用于钉钉app内，pc端自动登录。
 * 在登录页，如果判断发现是在钉钉app或者pc端app内，会自动调起js,获取code后，发出ajax请求给本action
 * */
class AjaxLoginAction extends BaseLoginAction
{
    public $dingapp = "dingtalk";
    public $loginUrl = "/site/login";
    public $returnCallback;
    public $loginCallback;
    
    public function init()
    {
        parent::init();
        if(!$this->dingapp instanceof Dingtalk) {
            throw new InvalidConfigException("钉钉app配置错误");
        }
        if(empty($this->returnCallback)) {
            // 返回标准格式的数据
            $this->returnCallback = function($msg, $url){
                $data = [
                    'code' => 0,
                    'msg' => "操作成功",
                    'data' => [
                        'redirect' => $url,
                    ],
                ];
                if($msg){
                    $data['code'] = 1;
                    $data['msg'] = $msg;
                }
                return $data;
            };
        }
        //判断returnCallback 与loginCallback是否为闭包
        if (!($this->loginCallback instanceof Closure) || !($this->returnCallback instanceof Closure)) {
            throw new InvalidConfigException("callback必须为闭包");
        }
    }


    public function run()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $code = Yii::$app->request->get('code', '');
        $backUrl = $this->loginUrl;
        if (empty($code)) {
            return call_user_func($this->returnCallback, "code获取不成功", $backUrl);
        }
        $data = Yii::$app->dingtalk->httpExec('/user/getuserinfo', ['code' => $code]);
        if ($data['errcode'] != 0) {
            return call_user_func($this->returnCallback, $data['errmsg'], $backUrl);
        }
        $ret = call_user_func($this->loginCallback, $data);
        return call_user_func($this->returnCallback, $ret, $backUrl);
    }
}
