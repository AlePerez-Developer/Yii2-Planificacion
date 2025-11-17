<?php
namespace app\modules\Planificacion\assets;

use frontend\assets\AppAsset;
use yii\web\AssetBundle;

class PlanificacionAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/Planificacion/assets';
    public $css = [
        'css/Planificacion.css',
        'css/btn_spinner.css',
        'css/dt_style.css'
    ];
    public $js = [
        'js/Validacion.js',
        'js/Planificacion.js',
        'js/common_Functions.js',
        'js/dt_Configuration.js',
        'js/msg_Functions.js',
        'js/ajax_Template.js'
    ];
    public $depends = [
        AppAsset::class,
        'yii\web\YiiAsset',
    ];
}



