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
    .programa-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .programa-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .programa-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .programa-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .programa-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .programa-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .programa-table thead th {
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
    .programa-table tbody td {
        font-size: 9px;
        color: #1e293b;
        padding: 6px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .programa-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .programa-num { width: 8%; text-align: center; color: #64748b; font-weight: 700; }
    .programa-code { width: 14%; font-family: monospace; font-weight: 700; color: #0369a1; text-align: center; }
    .programa-desc { width: 58%; }
    .programa-estado { width: 20%; text-align: center; }
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
    .programa-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
    }
</style>

<p class="programa-title">Programas</p>
<p class="programa-subtitle">Listado ejecutivo de registros · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="programa-summary">
    <tr>
        <td>Total registrados<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<table class="programa-table">
    <thead>
    <tr>
        <th class="programa-num">#</th>
        <th class="programa-code">Código</th>
        <th class="programa-desc">Descripción</th>
        <th class="programa-estado">Estado</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($registros)): ?>
        <tr>
            <td colspan="4" class="programa-empty">No hay programas registrados</td>
        </tr>
    <?php else: ?>
        <?php foreach ($registros as $i => $row): ?>
            <?php
            $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
            $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
            $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
            ?>
            <tr>
                <td class="programa-num"><?= $i + 1 ?></td>
                <td class="programa-code"><?= Html::encode($row['Codigo'] ?? '') ?></td>
                <td class="programa-desc"><?= Html::encode($row['Descripcion'] ?? '') ?></td>
                <td class="programa-estado">
                    <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
