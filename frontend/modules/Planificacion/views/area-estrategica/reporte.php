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
    .ae-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .ae-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .ae-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .ae-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .ae-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .ae-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .ae-table thead th {
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
    .ae-table tbody td {
        font-size: 9px;
        color: #1e293b;
        padding: 6px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .ae-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .ae-num { width: 8%; text-align: center; color: #64748b; font-weight: 700; }
    .ae-code { width: 14%; font-family: monospace; font-weight: 700; color: #0369a1; text-align: center; }
    .ae-desc { width: 58%; }
    .ae-estado { width: 20%; text-align: center; }
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
    .ae-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
        border: 1px solid #e2e8f0;
    }
</style>

<p class="ae-title">Áreas estratégicas</p>
<p class="ae-subtitle">Listado ejecutivo · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="ae-summary">
    <tr>
        <td>Total registradas<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<?php if (empty($registros)): ?>
    <div class="ae-empty">No hay áreas estratégicas registradas</div>
<?php else: ?>
    <table class="ae-table">
        <thead>
        <tr>
            <th class="ae-num">#</th>
            <th class="ae-code">Código</th>
            <th class="ae-desc">Descripción</th>
            <th class="ae-estado">Estado</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $i => $row): ?>
            <?php
            $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
            $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
            $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
            ?>
            <tr>
                <td class="ae-num"><?= $i + 1 ?></td>
                <td class="ae-code"><?= Html::encode($row['Codigo'] ?? '') ?></td>
                <td class="ae-desc"><?= Html::encode($row['Descripcion'] ?? '') ?></td>
                <td class="ae-estado">
                    <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
