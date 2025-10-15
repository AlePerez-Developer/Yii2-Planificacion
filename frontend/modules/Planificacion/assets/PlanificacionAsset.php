<?php
namespace app\modules\Planificacion\assets;

use frontend\assets\AppAsset;
use yii\web\AssetBundle;

class PlanificacionAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/Planificacion/assets';
    public $css = [
        'css/Planificacion.css',
        'css/btn_spinner.css'
    ];
    public $js = [
        'js/Validacion.js',
        'js/Planificacion.js',
    ];
    public $depends = [
        AppAsset::class,
        'yii\web\YiiAsset',
    ];
}

