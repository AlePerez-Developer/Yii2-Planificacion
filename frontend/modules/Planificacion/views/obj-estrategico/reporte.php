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
    .oe-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .oe-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .oe-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .oe-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .oe-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .oe-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .oe-table thead th {
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
    .oe-table tbody td {
        font-size: 8px;
        color: #1e293b;
        padding: 5px;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .oe-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }
    .oe-num { width: 4%; text-align: center; color: #64748b; font-weight: 700; }
    .oe-code { width: 8%; text-align: center; font-family: monospace; font-weight: 700; color: #0369a1; }
    .oe-obj { width: 24%; }
    .oe-prod { width: 16%; }
    .oe-area, .oe-pol { width: 16%; }
    .oe-gestion { width: 8%; text-align: center; }
    .oe-estado { width: 8%; text-align: center; }
    .oe-chip {
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
    .oe-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
    }
</style>

<p class="oe-title">Objetivos estratégicos institucionales</p>
<p class="oe-subtitle">Listado ejecutivo de registros · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="oe-summary">
    <tr>
        <td>Total registrados<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<table class="oe-table">
    <thead>
    <tr>
        <th class="oe-num">#</th>
        <th class="oe-code">Código</th>
        <th class="oe-obj">Objetivo</th>
        <th class="oe-prod">Producto</th>
        <th class="oe-area">Área</th>
        <th class="oe-pol">Política</th>
        <th class="oe-gestion">Gestión</th>
        <th class="oe-estado">Estado</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($registros)): ?>
        <tr>
            <td colspan="8" class="oe-empty">No hay objetivos estratégicos registrados</td>
        </tr>
    <?php else: ?>
        <?php foreach ($registros as $i => $row): ?>
            <?php
            $area = $row['areaEstrategica'] ?? [];
            $politica = $row['politicaEstrategica'] ?? [];
            $pei = $row['pei'] ?? [];
            $gestion = trim((string)($pei['GestionInicio'] ?? ''));
            $gestionFin = trim((string)($pei['GestionFin'] ?? ''));
            if ($gestion !== '' && $gestionFin !== '') {
                $gestion .= ' - ' . $gestionFin;
            }
            $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
            $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
            $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
            ?>
            <tr>
                <td class="oe-num"><?= $i + 1 ?></td>
                <td class="oe-code"><?= $texto($row['Compuesto'] ?? '') ?></td>
                <td class="oe-obj"><?= $texto($row['Objetivo'] ?? '') ?></td>
                <td class="oe-prod"><?= $texto($row['Producto'] ?? '') ?></td>
                <td class="oe-area">
                    <span class="oe-chip">&lt;<?= $texto($area['Codigo'] ?? '') ?>&gt;</span>
                    <?= $texto($area['Descripcion'] ?? '') ?>
                </td>
                <td class="oe-pol">
                    <span class="oe-chip">&lt;<?= $texto($politica['Codigo'] ?? '') ?>&gt;</span>
                    <?= $texto($politica['Descripcion'] ?? '') ?>
                </td>
                <td class="oe-gestion"><?= $texto($gestion) ?></td>
                <td class="oe-estado">
                    <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
