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

    $idPrograma = (string)($row['IdPrograma'] ?? '');
    if ($idPrograma === '') {
        $idPrograma = 'sin-programa';
    }

    if (!isset($grupos[$idPrograma])) {
        $grupos[$idPrograma] = [
            'codigo' => (string)(($row['programa']['Codigo'] ?? '') ?: ($row['codex'] ?? '')),
            'descripcion' => (string)(($row['programa']['Descripcion'] ?? '') ?: 'Sin descripción'),
            'items' => [],
        ];
    }
    $grupos[$idPrograma]['items'][] = $row;
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
    .proy-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .proy-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .proy-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    .proy-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .proy-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .proy-grupo {
        margin: 0 0 14px 0;
        page-break-inside: avoid;
    }
    .proy-grupo-header {
        background: #0369a1;
        color: #fff;
        padding: 7px 10px;
        border: 1px solid #0369a1;
        border-radius: 4px 4px 0 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .proy-grupo-codigo {
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
    .proy-grupo-meta {
        float: right;
        font-size: 9px;
        font-weight: 600;
        opacity: .9;
    }
    .proy-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 2px;
    }
    .proy-table thead th {
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
    .proy-table tbody td {
        font-size: 9px;
        color: #1e293b;
        padding: 5px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .proy-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .proy-num { width: 8%; text-align: center; color: #64748b; font-weight: 700; }
    .proy-code { width: 16%; font-family: monospace; font-weight: 700; color: #0369a1; text-align: center; }
    .proy-desc { width: 56%; }
    .proy-estado { width: 20%; text-align: center; }
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
    .proy-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
        border: 1px solid #e2e8f0;
    }
</style>

<p class="proy-title">Proyectos</p>
<p class="proy-subtitle">Listado ejecutivo agrupado por programa · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="proy-summary">
    <tr>
        <td>Total registrados<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<?php if (empty($grupos)): ?>
    <div class="proy-empty">No hay proyectos registrados</div>
<?php else: ?>
    <?php foreach ($grupos as $grupo): ?>
        <div class="proy-grupo">
            <div class="proy-grupo-header">
                <span class="proy-grupo-codigo">PROG <?= Html::encode($grupo['codigo']) ?></span>
                <?= Html::encode($grupo['descripcion']) ?>
                <span class="proy-grupo-meta"><?= count($grupo['items']) ?> registro(s)</span>
            </div>
            <table class="proy-table">
                <thead>
                <tr>
                    <th class="proy-num">#</th>
                    <th class="proy-code">Código</th>
                    <th class="proy-desc">Descripción</th>
                    <th class="proy-estado">Estado</th>
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
                        <td class="proy-num"><?= $i + 1 ?></td>
                        <td class="proy-code"><?= Html::encode($row['Codigo'] ?? '') ?></td>
                        <td class="proy-desc"><?= Html::encode($row['Descripcion'] ?? '') ?></td>
                        <td class="proy-estado">
                            <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
