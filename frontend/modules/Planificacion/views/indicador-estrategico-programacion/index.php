<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-programacion/index.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

if (isset($idObjEstrategico)) {
    $this->registerJsVar('idObjEstrategico', $idObjEstrategico);
}

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Programacion de indicadores estrategicos';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
    '';


$this->params['breadcrumbs'][] = [
    'label' => '/ Objetivos estrategicos',
    'url' => ['obj-estrategico/index']
];

$this->params['breadcrumbs'][] = [
    'label' => '/ Programacion de indicadores estrategicos',
];
?>

<div class="container-fluid mt-3">

    <div id="loaderIndicadores">
        <div class="acc-skeleton"></div>
        <div class="acc-skeleton"></div>
    </div>

    <div id="contenedorAccordion"></div>

</div>

<style>
    /* ===== ACCORDION ===== */

    :root{
        --primary:#2563eb;
        --soft:#eaf2ff;
        --text:#0f172a;
        --muted:#64748b;
        --success:#16a34a;
    }

    .acc-item{
        border-radius:20px;
        margin-bottom:14px;
        background:linear-gradient(135deg,#fff,var(--soft));
        box-shadow:0 10px 25px rgba(0,0,0,.05);
        transition:.25s;
    }

    .acc-item:hover{
        transform:translateY(-6px);
        box-shadow:0 18px 40px rgba(0,0,0,.12);
    }

    .acc-header{
        padding:18px;
        cursor:pointer;
        position:relative;
    }

    /* META ARRIBA IZQUIERDA */
    .meta-box-left{
        display:flex;
        gap:6px;
    }

    .meta-badge{
        background:#2563eb;
        color: #fff;
        border-radius:999px;
        padding:4px 10px;
        font-size:13px;
        font-weight:700;
    }

    .meta-badge-incompleta{
        background-color: ;
    }

    .dtic-item-sub{
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
    }

    /* DESCRIPCION */
    .acc-desc{
        font-size:15px;
        font-weight:800;
        margin:6px 0 10px;
    }

    /* FOOTER */
    .acc-footer{
        display:flex;
        justify-content:space-between;
        align-items:flex-end;
    }

    /* KPI */
    .kpi-circle{
        width:38px;
        height:38px;
        border-radius:12px;
        background:#64748b;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:800;
        font-size:14px;
        animation:kpiFade .6s ease;
    }

    /* RESULTADOS */
    .result-box{
        display:flex;
        flex-direction:column;
        align-items:flex-end;
        gap:4px;
    }

    .result-top{
        display:flex;
        gap:4px;
    }

    .badge-result{
        background:#8DBE5A;
        color:#fff;
        border-radius:10px;
        padding:4px 8px;
        font-size:11px;
        width: 120px;
        text-align: center;
    }

    /* BODY */
    .acc-body{
        display:none;
        padding:15px;
        background:#fff;
    }

    /* ===== TABS ===== */

    .tabs-nav{
        display:flex;
        gap:6px;
        margin-bottom:10px;
    }

    .tab-btn{
        flex:1;
        padding:10px;
        border:none;
        border-radius:10px;
        background:#f1f5f9;
        font-weight:700;
    }

    .tab-btn.active{
        background:#2563eb;
        color:#fff;
    }

    /* ===== TAB CONTENT ===== */

    .tab-pane{ display:none; }
    .tab-pane.active{ display:block; }

    /* ===== TABLE ===== */

    .table-container{
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 4px 12px rgba(0,0,0,.05);
    }

    .table-scroll{
        max-height:260px;
        overflow:auto;
    }

    .inputMeta,
    .inputMetaNueva{
        text-align:center;
        font-weight:700;
    }

    /* ===== NUEVA FILA ===== */

    .fila-nueva{
        background:#fff7ed;
    }

    /* ===== LOADER ===== */

    .acc-skeleton{
        height:90px;
        border-radius:14px;
        margin-bottom:10px;
        background:linear-gradient(90deg,#eee,#fff,#eee);
        background-size:200% 100%;
        animation:loading 1.2s infinite;
    }

    @keyframes loading{
        from{background-position:200%}
        to{background-position:-200%}
    }
</style>
