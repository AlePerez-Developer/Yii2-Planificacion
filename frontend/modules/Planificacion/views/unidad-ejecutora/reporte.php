<?php

use common\models\Estado;
use yii\helpers\Html;

/** @var array $registros */

$total = count($registros);
$vigentes = 0;
$caducos = 0;
$grupos = [];

foreach ($registros as $row) {
    if (($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE) {
        $vigentes++;
    } elseif (($row['CodigoEstado'] ?? '') === Estado::ESTADO_CADUCO) {
        $caducos++;
    }

    $idDa = (string)($row['IdDa'] ?? '');
    if ($idDa === '') {
        $idDa = 'sin-da';
    }

    if (!isset($grupos[$idDa])) {
        $grupos[$idDa] = [
            'codigo' => (string)($row['Da'] ?? ''),
            'descripcion' => (string)(($row['das']['Descripcion'] ?? '') ?: 'Sin descripción'),
            'items' => [],
        ];
    }
    $grupos[$idDa]['items'][] = $row;
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
    .ue-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .ue-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .ue-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    .ue-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .ue-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .ue-grupo {
        margin: 0 0 14px 0;
        page-break-inside: avoid;
    }
    .ue-grupo-header {
        background: #0369a1;
        color: #fff;
        padding: 7px 10px;
        border: 1px solid #0369a1;
        border-radius: 4px 4px 0 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .ue-grupo-codigo {
        display: inline-block;
        background: #fff;
        color: #0369a1;
        border-radius: 3px;
        padding: 1px 6px;
        margin-right: 6px;
        font-family: monospace;
        font-size: 10px;
        font-weight: 700;
    }
    .ue-grupo-meta {
        float: right;
        font-size: 9px;
        font-weight: 600;
        opacity: .9;
    }
    .ue-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 2px;
    }
    .ue-table thead th {
        background: #e0f2fe;
        color: #0c4a6e;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding: 5px 8px;
        border: 1px solid #bae6fd;
        text-align: left;
    }
    .ue-table tbody td {
        font-size: 9px;
        color: #1e293b;
        padding: 5px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .ue-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .ue-num { width: 8%; text-align: center; color: #64748b; font-weight: 700; }
    .ue-code { width: 16%; font-family: monospace; font-weight: 700; color: #0369a1; text-align: center; }
    .ue-desc { width: 56%; }
    .ue-estado { width: 20%; text-align: center; }
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
    .ue-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
        border: 1px solid #e2e8f0;
    }
</style>

<p class="ue-title">Unidades ejecutoras</p>
<p class="ue-subtitle">Listado ejecutivo agrupado por dirección administrativa · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="ue-summary">
    <tr>
        <td>Total registrados<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<?php if (empty($grupos)): ?>
    <div class="ue-empty">No hay unidades ejecutoras registradas</div>
<?php else: ?>
    <?php foreach ($grupos as $grupo): ?>
        <div class="ue-grupo">
            <div class="ue-grupo-header">
                <span class="ue-grupo-codigo">DA <?= Html::encode($grupo['codigo']) ?></span>
                <?= Html::encode($grupo['descripcion']) ?>
                <span class="ue-grupo-meta"><?= count($grupo['items']) ?> registro(s)</span>
            </div>
            <table class="ue-table">
                <thead>
                <tr>
                    <th class="ue-num">#</th>
                    <th class="ue-code">Código UE</th>
                    <th class="ue-desc">Descripción</th>
                    <th class="ue-estado">Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($grupo['items'] as $i => $row): ?>
                    <?php
                    $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
                    $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
                    $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
                    $compuesto = $row['Compuesto'] ?? (($row['Da'] ?? '') . '-' . ($row['Ue'] ?? ''));
                    ?>
                    <tr>
                        <td class="ue-num"><?= $i + 1 ?></td>
                        <td class="ue-code"><?= Html::encode($compuesto) ?></td>
                        <td class="ue-desc"><?= Html::encode($row['Descripcion'] ?? '') ?></td>
                        <td class="ue-estado">
                            <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
