<?php
namespace app\modules\Planificacion\assets;

use yii\web\AssetBundle;

class PlanificacionAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/Planificacion/assets';
    public $css = [
        '../../plugins/datatables/datatables.css',
        '../../plugins/jquerytreeview/jqtree.css',
        '../../../../vendor/almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.css',
       'css/Planificacion.css',
    ];
    public $js = [
        '../../plugins/datatables/datatables.js',
        '../../../../vendor/almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.all.js',
        '../../plugins/jquerytreeview/tree.jquery.js',
        '../../plugins/jquery-validation-1.19.5/dist/jquery.validate.min.js',
        'js/Validacion.js',
        'js/Planificacion.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}