<?php
namespace lspbupt\dingtalk\filters;
use yii\base\Behavior;
use \lspbupt\dingtalk\helpers\CryptHelper;
use yii\di\Instance;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use Yii;
use lspbupt\dingtalk\helpers\SysMsg;
/**
 * 钉钉回调事件，会自动对钉钉回调的数据进行校验，并解密, 并将结果存至post数据中,参见https://open-doc.dingtalk.com/docs/doc.htm?spm=a219a.7629140.0.0.mYj1A0&treeId=172&articleId=104975&docType=1
 **/
class CallbackFilter extends Behavior
{
    public $actions = [];
    public $crypt = 'dingcrypt';

    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    //在beforeAction之前处理 
    public function beforeAction($event)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $action = $event->action->id;
        if(in_array($action, $this->actions)){
            $data = Yii::$app->request->rawBody;
            $getArr = Yii::$app->request->get();
            if(empty($getArr['signature']) || empty($getArr['timestamp']) || empty($getArr['nonce'])) {
                return $this->outputError("F_DINGCALLBACK_ARG_ERR");
            }
            $this->crypt = Instance::ensure($this->crypt, CryptHelper::className()); 
            if(!$this->crypt) {
                return $this->outputError("F_DINGCALLBACK_CRYPT_ERR");
            }
            $data = json_decode($data, true);
            $encrypt = ArrayHelper::getValue($data, "encrypt", "");
            $ret = $this->crypt->decryptMsg($getArr['signature'], $getArr['timestamp'], $getArr['nonce'], $encrypt, $result);
            if($ret) {
                return $this->outputError($ret);     
            }
            Yii::$app->request->setBodyParams(json_decode($result, true)); 
            return true;
        }
    }

    private function outputError($ret) 
    {
        Yii::$app->response->data = SysMsg::getErrData($ret);
        Yii::$app->response->send();
        exit();  
    } 
}

SysMsg::register("F_DINGCALLBACK_ARG_ERR", "参数错误");
SysMsg::register("F_DINGCALLBACK_CRYPT_ERR","crypt错误");
