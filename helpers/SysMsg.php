<?php
namespace lspbupt\dingtalk\helpers;
use Yii;
/**
 * 提示文本处理
 * @package frameworks
 * @subpackage core
 */

/**
 * 提示文本处理
 * @package frameworks
 * @subpackage core
 */
class SysMsg
{
    /**
     * 提示信息模版定义
     */
    protected static $textTemplates = array();

    /**
     * 注册提示信息
     * @param String $index
     * @param String $textTemplate
     */
    public static function register($index, $textTemplate, $code = 1)
    {
        if (isset(self::$textTemplates[$index])) {
            Yii::warning("系统消息已定义：".$index, "sysmsg");
        } else {
            self::$textTemplates[$index] = array(
                'text' => $textTemplate,
                'code' => $code,
            );
        }
    }

    /**
     * 设置错误信息
     *
     * @param String|Array(String $msg, Mixed args1, args2, ..) $msg
     */
    public static function getErrMsg($msg = null)
    {
        $args = array();
        if (!$msg) {
            $msg = 'A_GENERAL_ERR';
        } else if (is_array($msg)) {
            $args = array_slice($msg, 1);
            $msg = $msg[0];
        }
        return SysMsg::get($msg, $args);
    }

    /**
     * 返回提示信息文本
     * @param String $index
     * @param Array() $args
     * @return String
     */
    public static function get($index, $args = array())
    {
        if (!is_array($args)) {
            $args = array($args);
        }
        if (is_array($index)) {
            foreach ($index as $key => $val) {
                if (isset($args[$key])) {
                    $index[$key] = self::get($val, $args[$key]);
                } else {
                    $index[$key] = self::get($val);
                }
            }
            return $index;
        }
        if (isset(self::$textTemplates[$index])) {
            return call_user_func_array('sprintf', array_merge(array(self::$textTemplates[$index]["text"]), $args));
        } else {
            return $index;
        }
    }

    public static function getError($index)
    {
        $errors = current($index);
        return self::getErrData($errors[0], 1);
    }

    public static function getErrData($index)
    {
        $temp = $index;
        if(is_array($index)) {
            $temp = $index[0];
        }
        
        if($index instanceof \yii\base\Model) {
            $temp = "A_GENERAL_ERR";
            $errors = $index->getErrors();
            foreach($errors as $key => $val) {
                return self::getErrData($val);
            }
            $index = $temp;
        }

        $data = array(
            'errcode' => 1,
            'errmsg' => $temp,
        );
        if(isset(self::$textTemplates[$temp])) {
            $data['errcode'] = self::$textTemplates[$temp]['code'];
            $data['errmsg'] = self::getErrMsg($index);
        }
        return $data;
    }

    public static function getOkData($data = array(), $index = "") 
    {
        return $data;
    }
}
SysMsg::register('A_GENERAL_ERR', '操作失败');
SysMsg::register('A_GENERAL_OK', '操作成功');
