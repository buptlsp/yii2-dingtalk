<?php
namespace lspbupt\dingtalk\helpers;
use lspbupt\dingtalk\helpers\Prpcrypt;
use Yii;
use yii\di\Instance;
use lspbupt\dingtalk\Dingtalk;
class CryptHelper extends \yii\base\Component
{
    public $aesKey;
    public $dingtalk = 'dingtalk';
    public $token;
    
    public function init()
    {
        parent::init();
        $this->dingtalk = Instance::ensure($this->dingtalk, Dingtalk::className());
    }

    public function getSuiteKey()
    {
        return $this->dingtalk->corpid;
    }

    public function encryptMsg($plain, $nonce, &$encryptMsg = "", $timeStamp=null)
    {
        $pc = $this->getPrpcrypt(); 
        $ret = $pc->encrypt($plain, $this->suiteKey, $encrypt);
        if ($ret) {
            return $ret;
        }
        if ($timeStamp == null) {
            $timeStamp = time();
        }
        
        $sign = self::getSHA1($this->token, $timeStamp, $nonce, $encrypt);
        $encryptMsg = [
            "msg_signature" => $sign,
            "encrypt" => $encrypt,
            "timeStamp" => $timeStamp,
            "nonce" => $nonce
        ];
        return false;
    }
    
    public function decryptMsg($signature, $timeStamp = null, $nonce, $encrypt, &$decryptMsg)
    {
        if (strlen($this->aesKey) != 43) {
            return "aesKey错误";
        }
        empty($timeStamp) && $timeStamp = time();
        $checkSign = self::getSHA1($this->token, $timeStamp, $nonce, $encrypt);
        if ($checkSign != $signature) {
            return "签名错误";
        }
        $pc = $this->getPrpcrypt(); 
        $ret = $pc->decrypt($encrypt, $this->suiteKey, $decryptMsg);
        if ($ret) {
            return $ret;
        }
        return false;
    }

    public static function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
    {
        $arr = [$encrypt_msg, $token, $timestamp, $nonce];
        sort($arr, SORT_STRING);
        $str = implode($arr);
        return sha1($str);
    }

    public function getPrpcrypt()
    {
        $pc = Yii::createObject([
            'class' => '\lspbupt\dingtalk\helpers\Prpcrypt',
            'key' => base64_decode($this->aesKey . "=")
        ]);
        return $pc;
    }
}
