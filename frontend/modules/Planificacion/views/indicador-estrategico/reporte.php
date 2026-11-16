<?php

use common\models\Estado;
use yii\helpers\Html;

/** @var array $registros */

$total = count($registros);
$vigentes = 0;
$caducos = 0;
foreach ($registros as $row) {
    if (($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE) {
        $vigentes++;
    } elseif (($row['CodigoEstado'] ?? '') === Estado::ESTADO_CADUCO) {
        $caducos++;
    }
}
?>
<style>
    .reporte-header { height: 70px; }
    .reporte-footer {
        font-size: 9px;
        color: #444;
        border-top: 1px solid #cbd5e1;
        padding-top: 4px;
    }
    .ie-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .ie-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .ie-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .ie-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .ie-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .ie-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .ie-table thead th {
        background: #0f172a;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding: 7px 8px;
        border: 1px solid #0f172a;
        text-align: left;
    }
    .ie-table tbody td {
        font-size: 9px;
        color: #1e293b;
        padding: 6px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .ie-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .ie-num { width: 6%; text-align: center; color: #64748b; font-weight: 700; }
    .ie-code { width: 10%; font-family: monospace; font-weight: 700; color: #0369a1; text-align: center; }
    .ie-desc { width: 34%; }
    .ie-aei { width: 34%; }
    .ie-estado { width: 16%; text-align: center; }
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .badge-vigente {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .badge-caduco {
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
    }
    .ie-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
        border: 1px solid #e2e8f0;
    }
</style>

<p class="ie-title">Indicadores estratégicos</p>
<p class="ie-subtitle">Listado ejecutivo · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="ie-summary">
    <tr>
        <td>Total registrados<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<?php if (empty($registros)): ?>
    <div class="ie-empty">No hay indicadores estratégicos registrados</div>
<?php else: ?>
    <table class="ie-table">
        <thead>
        <tr>
            <th class="ie-num">#</th>
            <th class="ie-code">Código</th>
            <th class="ie-desc">Descripción</th>
            <th class="ie-aei">AEI</th>
            <th class="ie-estado">Estado</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $i => $row): ?>
            <?php
            $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
            $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
            $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
            $descripcion = $row['Descripcion']
                ?? ($row['indicador']['Descripcion'] ?? '');
            $accionCodigo = $row['AccionCodigo']
                ?? ($row['accionesEstrategicas']['Codigo'] ?? '—');
            $accionDesc = $row['AccionDescripcion']
                ?? ($row['accionesEstrategicas']['Descripcion'] ?? '');
            $aei = 'AEI: ' . $accionCodigo . ' - ' . $accionDesc;
            ?>
            <tr>
                <td class="ie-num"><?= $i + 1 ?></td>
                <td class="ie-code"><?= Html::encode($row['Codigo'] ?? '') ?></td>
                <td class="ie-desc"><?= Html::encode($descripcion) ?></td>
                <td class="ie-aei"><?= Html::encode($aei) ?></td>
                <td class="ie-estado">
                    <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
