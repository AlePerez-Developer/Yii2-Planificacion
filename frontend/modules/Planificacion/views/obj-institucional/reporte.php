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

$texto = static function ($valor): string {
    return Html::encode(trim((string)$valor));
};
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
    .oi-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .oi-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .oi-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .oi-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .oi-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .oi-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .oi-table thead th {
        background: #0f172a;
        color: #fff;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding: 6px 5px;
        border: 1px solid #0f172a;
        text-align: left;
    }
    .oi-table tbody td {
        font-size: 8px;
        color: #1e293b;
        padding: 5px;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .oi-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }
    .oi-num { width: 4%; text-align: center; color: #64748b; font-weight: 700; }
    .oi-code { width: 10%; text-align: center; font-family: monospace; font-weight: 700; color: #0f766e; }
    .oi-obj { width: 24%; }
    .oi-prod { width: 18%; }
    .oi-oe { width: 28%; }
    .oi-gestion { width: 8%; text-align: center; }
    .oi-estado { width: 8%; text-align: center; }
    .oi-chip {
        font-family: monospace;
        font-weight: 700;
        color: #1d4ed8;
    }
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 7px;
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
    .oi-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
    }
</style>

<p class="oi-title">Objetivos institucionales</p>
<p class="oi-subtitle">Listado ejecutivo de registros · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="oi-summary">
    <tr>
        <td>Total registrados<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<table class="oi-table">
    <thead>
    <tr>
        <th class="oi-num">#</th>
        <th class="oi-code">Código</th>
        <th class="oi-obj">Objetivo institucional</th>
        <th class="oi-prod">Producto</th>
        <th class="oi-oe">Objetivo estratégico</th>
        <th class="oi-gestion">Gestión</th>
        <th class="oi-estado">Estado</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($registros)): ?>
        <tr>
            <td colspan="7" class="oi-empty">No hay objetivos institucionales registrados</td>
        </tr>
    <?php else: ?>
        <?php foreach ($registros as $i => $row): ?>
            <?php
            $oe = $row['objetivosEstrategicos'] ?? [];
            $gestion = $row['peiGestion']['Gestion'] ?? '';
            $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
            $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
            $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
            ?>
            <tr>
                <td class="oi-num"><?= $i + 1 ?></td>
                <td class="oi-code"><?= $texto($row['Compuesto'] ?? '') ?></td>
                <td class="oi-obj"><?= $texto($row['Objetivo'] ?? '') ?></td>
                <td class="oi-prod"><?= $texto($row['Producto'] ?? '') ?></td>
                <td class="oi-oe">
                    <span class="oi-chip">&lt;<?= $texto($oe['Codigo'] ?? '') ?>&gt;</span>
                    <?= $texto($oe['Objetivo'] ?? '') ?>
                </td>
                <td class="oi-gestion"><?= $texto($gestion) ?></td>
                <td class="oi-estado">
                    <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
