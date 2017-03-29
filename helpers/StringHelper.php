<?php
namespace lspbupt\dingtalk\helpers;
class StringHelper
{
    public static function parseJsonData($str)
    {
        $str = trim($str);
        $str = trim($str, "{");
        $str = trim($str, "}");
        $arr = explode(",", $str);
        $dataArr = [];
        foreach($arr as $val) {
            $tempArr = explode(":", $val);
            if(count($tempArr) == 2) {
                $dataArr[$tempArr[0]] = $tempArr[1];
            }
        }
        return $dataArr;
    }
}
