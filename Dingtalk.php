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
                $token = static::getTokenFromCache();
                $params['access_token'] = $token;
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
        return $this->setGet()->httpExec("/gettoken", ['corpid'=>$this->corpid, 'corpsecret' => $this->corpsecret]);
    }

    public static function getTokenFromCache()
    {
        $obj = new self();
        $token = $obj->cache->get(self::DINGTALK_CACHEKEY, "");
        if($token) {
            return $token;
        }
        $arr = $obj->getToken();
        if($arr['errcode'] == 0) {
            $obj->cache->set(self::DINGTALK_CACHEKEY, $arr['access_token'], 3600);
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

    public static function getJsapiTicketFromCache()
    {
        $obj = new self();
        $jsapitoken = $obj->cache->get(self::DINGTALK_JSAPI_CACHEKEY, "");
        if($jsapitoken) {
            return $jsapitoken;
        }
        $arr = $obj->getJsapiTicket();
        if($arr['errcode'] == 0) {
            $jsapitoken = $arr["ticket"];
            $expire = $arr['expires_in'];  
            $obj->cache->set(self::DINGTALK_JSAPI_CACHEKEY, $jsapitoken, $expire-60);
            return $jsapitoken;
        }
        return "";
    }

    public function JsSign(&$arr=[]) 
    {
        empty($arr['url']) && $arr['url'] = "";
        empty($arr['timestamp']) && $arr['timestamp'] = time();
        empty($arr['noncestr']) && $arr['noncestr'] = \Yii::$app->security->generateRandomString(10);
        empty($arr['jsapi_ticket']) && $arr['jsapi_ticket'] = self::getJsapiTicketFromCache();
        $plain = 'jsapi_ticket=' . $arr['jsapi_ticket'] .
            '&noncestr=' . $arr['noncestr'] .
            '&timestamp=' . $arr['timestamp'] .
            '&url=' . $arr['url'];
            echo '<pre>'.htmlspecialchars($plain).'</pre>';
        return sha1($plain);
    }
}
