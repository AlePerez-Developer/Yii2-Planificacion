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
    .reporte-header {
        height: 70px;
    }
    .reporte-footer {
        font-size: 9px;
        color: #444;
        border-top: 1px solid #cbd5e1;
        padding-top: 4px;
    }
    .da-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .da-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .da-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .da-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .da-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .da-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .da-table thead th {
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
    .da-table tbody td {
        font-size: 9px;
        color: #1e293b;
        padding: 6px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .da-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }
    .da-num {
        width: 8%;
        text-align: center;
        color: #64748b;
        font-weight: 700;
    }
    .da-code {
        width: 14%;
        font-family: monospace;
        font-weight: 700;
        color: #0369a1;
        text-align: center;
    }
    .da-desc {
        width: 58%;
    }
    .da-estado {
        width: 20%;
        text-align: center;
    }
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
    .da-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
    }
</style>

<p class="da-title">Direcciones administrativas</p>
<p class="da-subtitle">Listado ejecutivo de registros · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="da-summary">
    <tr>
        <td>Total registrados<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<table class="da-table">
    <thead>
    <tr>
        <th class="da-num">#</th>
        <th class="da-code">Código</th>
        <th class="da-desc">Descripción</th>
        <th class="da-estado">Estado</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($registros)): ?>
        <tr>
            <td colspan="4" class="da-empty">No hay direcciones administrativas registradas</td>
        </tr>
    <?php else: ?>
        <?php foreach ($registros as $i => $row): ?>
            <?php
            $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
            $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
            $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
            ?>
            <tr>
                <td class="da-num"><?= $i + 1 ?></td>
                <td class="da-code"><?= Html::encode($row['Da'] ?? '') ?></td>
                <td class="da-desc"><?= Html::encode($row['Descripcion'] ?? '') ?></td>
                <td class="da-estado">
                    <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
