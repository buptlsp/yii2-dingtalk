<?php
namespace lspbupt\dingtalk;
use \Yii;
use yii\caching\Cache;
use yii\di\Instance;
class Dingtalk extends \lspbupt\curl\CurlHttp
{
    public $corpid = "";
    public $corpsecret = "";
    public $agentid = "";
    public $host = "oapi.dingtalk.com";
    public $protocol = "https";
    
    public $appkey = "";
    public $appsecret = "";


    public $cache = 'cache';
    const DINGTALK_CACHEKEY = "dingtalk_cachekey";
    const DINGTALK_JSAPI_CACHEKEY = "dingtalk_jsapi_cachekey";

    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::className());
        $this->beforeRequest = function($params, $curlhttp) {
            $action = $curlhttp->getAction();
            if($action != "/gettoken") {
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
        if(!empty($this->corpid) && !empty($this->corpsecret)) {
            $arr = [
                'corpid' => $this->corpid,
                'corpsecret' => $this->corpsecret,
            ];
        }else {
            $arr = [
                'appkey' => $this->appkey,
                'appsecret' => $this->appsecret,
            ];
        }
        return $this->setGet()->httpExec("/gettoken", $arr);
    }

    public function getCacheKey()
    {
        $id  = $this->corpid.":".$this->appkey;
        return self::DINGTALK_CACHEKEY.$id;
    }

    public function getJsCacheKey()
    {
        $id  = empty($this->appkey) ? $this->corpid : $this->appkey;
        return self::DINGTALK_JSAPI_CACHEKEY.$id;
    }
    
    public function getTokenFromCache()
    {
        $token = $this->cache->get($this->cacheKey, "");
        if($token) {
            return $token;
        }
        $arr = $this->getToken();
        if($arr['errcode'] == 0) {
            $this->cache->set($this->cacheKey, $arr['access_token'], 3600);
            return $arr['access_token'];
        }
        return "";
    }

    public function getJsapiTicket()
    {
        return $this->setGet()
            ->setProtocol("https")
            ->httpExec("/get_jsapi_ticket", []); 
    }

    public function getJsapiTicketFromCache()
    {
        $jsapitoken = $this->cache->get($this->jsCacheKey, "");
        if($jsapitoken) {
            return $jsapitoken;
        }
        $arr = $this->getJsapiTicket();
        if($arr['errcode'] == 0) {
            $jsapitoken = $arr["ticket"];
            $expire = $arr['expires_in'];  
            $this->cache->set($this->jsCacheKey, $jsapitoken, $expire-60);
            return $jsapitoken;
        }
        return "";
    }

    public function JsSign(&$arr=[]) 
    {
        empty($arr['url']) && $arr['url'] = "";
        empty($arr['timestamp']) && $arr['timestamp'] = time();
        empty($arr['noncestr']) && $arr['noncestr'] = \Yii::$app->security->generateRandomString(10);
        empty($arr['jsapi_ticket']) && $arr['jsapi_ticket'] = $this->getJsapiTicketFromCache();
        $plain = 'jsapi_ticket=' . $arr['jsapi_ticket'] .
            '&noncestr=' . $arr['noncestr'] .
            '&timestamp=' . $arr['timestamp'] .
            '&url=' . $arr['url'];
        return sha1($plain);
    }

    //获取部门列表
    public function getDepartmentList()
    {
        return $this->setGet()->httpExec("/department/list", []);
    }

    //获取部门详情
    public function getDepartmentDetail($departmentid)
    {
        return $this->setGet()->httpExec("/department/get", ['id' => $departmentid]);
    }

    public function sendTextMsg($userid, $party, $msg)
    {
        $users = "";
        if(!empty($userid)) {
            if(is_array($userid)) {
                $users = implode("|", $userid);
            }else {
                $users = $userid;
            }
        }
        $partys = "";
        if(!empty($party)) {
            if(is_array($party)) {
                $partys = implode("|", $party);
            }
        }
        $arr = [
            'touser' => $users,
            'toparty' => $partys,
            'agentid' => $this->agentid,
            'msgtype' => 'text',
            'text' => ['content' => $msg],
        ];
        return $this->setPostJson()->httpExec("/message/send", $arr); 
    }
}
