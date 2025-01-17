<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@vendor';
    public $css = [
        'datatables.net/datatables.net-bs5/css/dataTables.bootstrap5.css',
        'datatables.net/datatables.net-responsive-bs5/css/responsive.bootstrap5.css',
        'datatables.net/datatables.net-select-bs5/css/select.bootstrap5.css',
        'almasaeed2010/adminlte/plugins/select2/css/select2.css',
        'almasaeed2010/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.css',
        'almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.css',
        'almasaeed2010/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.css',
        'almasaeed2010/adminlte/plugins/toastr/toastr.css',
        'almasaeed2010/adminlte/plugins/jquery-ui/jquery-ui.css',
        'almasaeed2010/adminlte/plugins/fontawesome-free/css/fontawesome.css'
    ];
    public $js = [
        'datatables.net/datatables.net/js/dataTables.js',
        'datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.js',
        'datatables.net/datatables.net-responsive/js/dataTables.responsive.min.js',
        'datatables.net/datatables.net-responsive-bs5/js/responsive.bootstrap5.js',
        'datatables.net/datatables.net-select/js/dataTables.select.js',
        'datatables.net/datatables.net-select-bs5/js/select.bootstrap5.js',
        'almasaeed2010/adminlte/plugins/jquery-validation/jquery.validate.js',
        'almasaeed2010/adminlte/plugins/jquery-validation/additional-methods.js',
        'almasaeed2010/adminlte/plugins/select2/js/select2.js',
        'almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.js',
        'almasaeed2010/adminlte/plugins/toastr/toastr.min.js',
        'almasaeed2010/adminlte/plugins/jquery-ui/jquery-ui.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\bootstrap5\BootstrapPluginAsset'
    ];
}