<?php
namespace lspbupt\dingtalk;
use \Yii;
use yii\caching\Cache;
use yii\di\Instance;
class DingApp extends \lspbupt\curl\CurlHttp
{
    public $appid = "";
    public $appsecret = "";
    public $host = "oapi.dingtalk.com";
    public $protocol = "https";
    public $redirectUrl = "";
    public $cache = 'cache';
    const DINGTALK_CACHEKEY = "dingapp_cachekey";

    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::className());
        $this->beforeRequest = function($params, $curlhttp) {
            $action = $curlhttp->getAction();
            if($action != "/sns/gettoken") {
                $ch = clone $curlhttp;
                $token = $ch->getTokenFromCache();
                if(strpos($action, "?") != 0) {
                    $curlhttp->setAction($action."&access_token=".$token);
                } else {
                    $curlhttp->setAction($action."?access_token=".$token);
                }
            }
            return $params; 
        };
        $this->afterRequest = function($output, $curlhttp) {
            $data = json_decode($output, true);
            if(empty($output)) {
                $data = [
                    'errcode' => 1,
                    'errmsg' => '网络错误!',
                ]; 
            }
            return $data;
        };
    }

    public function getToken()
    {
        return $this->setGet()->httpExec("/sns/gettoken", ['appid'=>$this->appid, 'appsecret' => $this->appsecret]);
    }

    public function getTokenFromCache()
    {
        $token = $this->cache->get(self::DINGTALK_CACHEKEY.$this->appid, "");
        if($token) {
            return $token;
        }
        $arr = $this->getToken();
        if($arr['errcode'] == 0) {
            $this->cache->set(self::DINGTALK_CACHEKEY.$this->appid, $arr['access_token'], 3600);
            return $arr['access_token'];
        }
        return "";
    }

    public function getPersistentCode($tmpCode="")
    {
        $tmpData = [
            'tmp_auth_code' => $tmpCode,
        ];
        return $this->setPostJson()->httpExec("/sns/get_persistent_code", $tmpData);
    }

    public function getSnsToken($openid, $pCode)
    {
        $params = [
            'openid' => $openid,
            'persistent_code' => $pCode,
        ];
        return $this->setPostJson()->httpExec("/sns/get_sns_token", $params);
    }

    public function getUserInfo($snsToken)
    {
        $params = ['sns_token' => $snsToken];
        return $this->setGet()->httpExec("/sns/getuserinfo", $params);
    }

    public function getUserInfoByTmpCode($tmpCode, &$userInfo = [])
    {
        $pCodeData = $this->getPersistentCode($tmpCode);
        if($pCodeData['errcode']!=0) {
            return $pCodeData['errmsg'];
        }
        $snsData = $this->getSnsToken($pCodeData['openid'], $pCodeData['persistent_code']);
        if($snsData['errcode'] != 0) {
            return $snsData['errmsg'];
        } 
        $userInfo = $this->getUserInfo($snsData['sns_token']);
        if($userInfo['errcode'] != 0) {
            return $userInfo['errmsg'];
        }
        return false;
    }
}
