<?php
namespace app\modules\Planificacion\assets;

use frontend\assets\AppAsset;
use yii\web\AssetBundle;

class PlanificacionAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/Planificacion/assets'; //para local
    //public $basePath = '@webroot';   //produccion
    //public $baseUrl = '@web';        //produccion

    public $css = [
        'css/Planificacion.css',
        'css/btn_spinner.css',
        'css/dt_style.css',
        'css/dtic-style.css',
    ];
    public $js = [
        'js/Validacion.js',
        'js/Planificacion.js',
        'js/common_Functions.js',
        'js/msg_Functions.js',
        'js/ajax_Template.js',
        'js/dt_Configuration.js'
    ];
    public $depends = [
        AppAsset::class,
        'yii\web\YiiAsset',
    ];
}



