<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        '../../vendor/datatables.net/datatables.net-bs5/css/dataTables.bootstrap5.css',
        '../../vendor/datatables.net/datatables.net-responsive-bs5/css/responsive.bootstrap5.css',
        '../../vendor/datatables.net/datatables.net-select-bs5/css/select.bootstrap5.css',
        '../../vendor/almasaeed2010/adminlte/plugins/select2/css/select2.css',
        '../../vendor/almasaeed2010/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.css',
        '../../vendor/almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.css',
        '../../vendor/almasaeed2010/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.css',
        '../../vendor/almasaeed2010/adminlte/plugins/jquery-ui/jquery-ui.css',
        '../../vendor/almasaeed2010/adminlte/plugins/fontawesome-free/css/fontawesome.css'
    ];
    public $js = [
        '../../vendor/datatables.net/datatables.net/js/dataTables.js',
        '../../vendor/datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.js',
        '../../vendor/datatables.net/datatables.net-responsive/js/dataTables.responsive.min.js',
        '../../vendor/datatables.net/datatables.net-responsive-bs5/js/responsive.bootstrap5.js',
        '../../vendor/datatables.net/datatables.net-select/js/dataTables.select.js',
        '../../vendor/datatables.net/datatables.net-select-bs5/js/select.bootstrap5.js',
        '../../vendor/almasaeed2010/adminlte/plugins/jquery-validation/jquery.validate.js',
        '../../vendor/almasaeed2010/adminlte/plugins/select2/js/select2.js',
        '../../vendor/almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.js',
        '../../vendor/almasaeed2010/adminlte/plugins/jquery-ui/jquery-ui.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\bootstrap5\BootstrapPluginAsset'
    ];
}
