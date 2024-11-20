<?php
namespace app\modules\PlanificacionCH\assets;

use frontend\assets\AppAsset;
use yii\web\AssetBundle;

class PlanificacionCHAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/PlanificacionCH/assets';
    public $css = [
       'css/PlanificacionCH.css',
    ];
    public $js = [
        'js/ValidacionCH.js',
        'js/PlanificacionCH.js',
    ];
    public $depends = [
        AppAsset::class,
        'yii\web\YiiAsset',
    ];
}