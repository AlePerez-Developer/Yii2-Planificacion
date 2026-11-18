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
    .reporte-header { height: 70px; }
    .reporte-footer {
        font-size: 9px;
        color: #444;
        border-top: 1px solid #cbd5e1;
        padding-top: 4px;
    }
    .oes-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .oes-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .oes-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .oes-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .oes-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .oes-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .oes-table thead th {
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
    .oes-table tbody td {
        font-size: 8px;
        color: #1e293b;
        padding: 5px;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .oes-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .oes-num { width: 4%; text-align: center; color: #64748b; font-weight: 700; }
    .oes-code { width: 12%; text-align: center; font-family: monospace; font-weight: 700; color: #c2410c; }
    .oes-obj { width: 30%; }
    .oes-oi { width: 30%; }
    .oes-gestion { width: 10%; text-align: center; }
    .oes-estado { width: 10%; text-align: center; }
    .oes-chip {
        font-family: monospace;
        font-weight: 700;
        color: #0f766e;
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
    .oes-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
    }
</style>

<p class="oes-title">Objetivos específicos</p>
<p class="oes-subtitle">Listado ejecutivo de registros · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="oes-summary">
    <tr>
        <td>Total registrados<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<table class="oes-table">
    <thead>
    <tr>
        <th class="oes-num">#</th>
        <th class="oes-code">Código</th>
        <th class="oes-obj">Objetivo específico</th>
        <th class="oes-oi">Objetivo institucional</th>
        <th class="oes-gestion">Gestión</th>
        <th class="oes-estado">Estado</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($registros)): ?>
        <tr>
            <td colspan="6" class="oes-empty">No hay objetivos específicos registrados</td>
        </tr>
    <?php else: ?>
        <?php foreach ($registros as $i => $row): ?>
            <?php
            $oi = $row['objetivosInstitucionales'] ?? [];
            $gestion = $row['peiGestion']['Gestion'] ?? '';
            $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
            $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
            $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
            ?>
            <tr>
                <td class="oes-num"><?= $i + 1 ?></td>
                <td class="oes-code"><?= $texto($row['Compuesto'] ?? '') ?></td>
                <td class="oes-obj"><?= $texto($row['Objetivo'] ?? '') ?></td>
                <td class="oes-oi">
                    <span class="oes-chip">&lt;<?= $texto($oi['Codigo'] ?? '') ?>&gt;</span>
                    <?= $texto($oi['Objetivo'] ?? '') ?>
                </td>
                <td class="oes-gestion"><?= $texto($gestion) ?></td>
                <td class="oes-estado">
                    <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
