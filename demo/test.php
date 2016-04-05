<?php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../Dingtalk.php');


$dingding = new \lspbupt\dingtalk\Dingtalk();
var_dump($dingding->setGet()->httpExec("/get_jsapi_ticket", []));
