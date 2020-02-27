<?php
namespace lspbupt\dingtalk\assets;

use yii\web\AssetBundle;
class AutoLoginAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/../static';
    public $js = [
        'js/login.js'
    ];
}
