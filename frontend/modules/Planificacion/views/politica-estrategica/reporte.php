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

    $idArea = (string)($row['IdAreaEstrategica'] ?? '');
    if ($idArea === '') {
        $idArea = 'sin-area';
    }

    if (!isset($grupos[$idArea])) {
        $grupos[$idArea] = [
            'codigo' => (string)($row['areaEstrategica']['Codigo'] ?? ''),
            'descripcion' => (string)(($row['areaEstrategica']['Descripcion'] ?? '') ?: 'Sin descripción'),
            'items' => [],
        ];
    }
    $grupos[$idArea]['items'][] = $row;
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
    .pe-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .pe-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .pe-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    .pe-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .pe-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .pe-grupo {
        margin: 0 0 14px 0;
        page-break-inside: avoid;
    }
    .pe-grupo-header {
        background: #0369a1;
        color: #fff;
        padding: 7px 10px;
        border: 1px solid #0369a1;
        border-radius: 4px 4px 0 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .pe-grupo-codigo {
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
    .pe-grupo-meta {
        float: right;
        font-size: 9px;
        font-weight: 600;
        opacity: .9;
    }
    .pe-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 2px;
    }
    .pe-table thead th {
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
    .pe-table tbody td {
        font-size: 9px;
        color: #1e293b;
        padding: 5px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .pe-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .pe-num { width: 8%; text-align: center; color: #64748b; font-weight: 700; }
    .pe-code { width: 16%; font-family: monospace; font-weight: 700; color: #0369a1; text-align: center; }
    .pe-desc { width: 56%; }
    .pe-estado { width: 20%; text-align: center; }
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
    .pe-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
        border: 1px solid #e2e8f0;
    }
</style>

<p class="pe-title">Políticas estratégicas</p>
<p class="pe-subtitle">Listado ejecutivo agrupado por área estratégica · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="pe-summary">
    <tr>
        <td>Total registradas<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<?php if (empty($grupos)): ?>
    <div class="pe-empty">No hay políticas estratégicas registradas</div>
<?php else: ?>
    <?php foreach ($grupos as $grupo): ?>
        <div class="pe-grupo">
            <div class="pe-grupo-header">
                <span class="pe-grupo-codigo">ÁREA <?= Html::encode($grupo['codigo']) ?></span>
                <?= Html::encode($grupo['descripcion']) ?>
                <span class="pe-grupo-meta"><?= count($grupo['items']) ?> registro(s)</span>
            </div>
            <table class="pe-table">
                <thead>
                <tr>
                    <th class="pe-num">#</th>
                    <th class="pe-code">Código</th>
                    <th class="pe-desc">Descripción</th>
                    <th class="pe-estado">Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($grupo['items'] as $i => $row): ?>
                    <?php
                    $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
                    $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
                    $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
                    ?>
                    <tr>
                        <td class="pe-num"><?= $i + 1 ?></td>
                        <td class="pe-code"><?= Html::encode($row['Codigo'] ?? '') ?></td>
                        <td class="pe-desc"><?= Html::encode($row['Descripcion'] ?? '') ?></td>
                        <td class="pe-estado">
                            <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
