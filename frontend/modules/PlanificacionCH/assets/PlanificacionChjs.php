<?php
namespace app\modules\PlanificacionCH\assets;

use frontend\assets\AppAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class PlanificacionChjs extends AssetBundle
{
    public $sourcePath = '@app/modules/PlanificacionCH';
    public $js = [
        'js/planificar-carga-horaria/planificarcargahoraria.js',
        'js/planificar-carga-horaria/dt-Grupos.js',
        'js/planificar-carga-horaria/s2-Declarations.js',
        'js/planificar-carga-horaria/dt-Materias.js',
    ];
    public $depends = [
        JqueryAsset::class,
        AppAsset::class,
        'yii\web\YiiAsset',
    ];
}