<?php

use yii\helpers\Html;

/** @var int $gestion */
/** @var string $unidad */
/** @var array $grupos */
/** @var float $totalGeneral */

$fmt = static fn(float $valor): string => number_format($valor, 2, ',', '.');
?>
<style>
    .reporte-header {
        height: 70px;
        border-bottom: 1px solid #ffffff;
    }
    .reporte-footer {
        font-size: 8px;
        color: #444;
        border-top: 1px solid #ccc;
        padding-top: 4px;
    }
    .reporte-titulo {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        margin: 0 0 4px 0;
        text-transform: uppercase;
    }
    .reporte-subtitulo {
        text-align: center;
        font-size: 9px;
        margin: 0 0 8px 0;
        color: #333;
    }
    .ogi-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .ogi-table th,
    .ogi-table td {
        border: 1px solid #4a4a4a;
        font-size: 7.5px;
        padding: 3px 4px;
        vertical-align: middle;
    }
    .ogi-table thead th {
        background: #d9d9d9;
        text-align: center;
        font-weight: bold;
        line-height: 1.25;
    }
    .ogi-group {
        background: #bfbfbf;
        font-weight: bold;
        font-size: 8px;
        text-align: left;
    }
    .ogi-num {
        text-align: center;
    }
    .ogi-text {
        text-align: justify;
        line-height: 1.3;
    }
    .ogi-money {
        text-align: right;
        white-space: nowrap;
    }
    .ogi-total td {
        background: #e6e6e6;
        font-weight: bold;
    }
    .ogi-vacio {
        text-align: center;
        font-style: italic;
        color: #666;
        padding: 12px;
        font-size: 9px;
    }
</style>

<p class="reporte-titulo">Formulario 1 — Objetivo de gestión institucional</p>
<p class="reporte-subtitulo">
    Gestión <?= Html::encode((string)$gestion) ?>
    <?php if ($unidad !== ''): ?>
        | Unidad: <?= Html::encode($unidad) ?>
    <?php endif; ?>
</p>

<table class="ogi-table">
    <thead>
    <tr>
        <th colspan="4">Indicador (Resultado Esperado - Producto) de la Gestión</th>
        <th rowspan="2" width="9%">(Bien, Norma o Servicio)</th>
        <th colspan="5">Programación trimestral del producto (cantidad o porcentaje)</th>
        <th rowspan="2" width="10%">Presupuesto programado por OGI</th>
    </tr>
    <tr>
        <th width="5%">Cod</th>
        <th width="32%">Denominación</th>
        <th width="7%">Tipo</th>
        <th width="8%">Categoría</th>
        <th width="5%">I</th>
        <th width="5%">II</th>
        <th width="5%">III</th>
        <th width="5%">IV</th>
        <th width="9%">Meta anual</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($grupos)): ?>
        <tr>
            <td colspan="11" class="ogi-vacio">No hay indicadores programados para el contexto activo.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($grupos as $grupo): ?>
            <tr>
                <td colspan="11" class="ogi-group">
                    Cod. Articulación: <?= Html::encode($grupo['codArticulacion']) ?>
                    &nbsp;|&nbsp;
                    Objetivo Gestión Institucional (Acción de Corto Plazo Institucional) Gestión <?= Html::encode((string)$gestion) ?>:
                    <?= Html::encode($grupo['objetivo']) ?>
                </td>
            </tr>
            <?php foreach ($grupo['indicadores'] as $indicador): ?>
                <tr>
                    <td class="ogi-num"><?= Html::encode((string)$indicador['codigo']) ?></td>
                    <td class="ogi-text"><?= Html::encode($indicador['denominacion']) ?></td>
                    <td class="ogi-num"><?= Html::encode($indicador['tipo']) ?></td>
                    <td class="ogi-num"><?= Html::encode($indicador['categoria']) ?></td>
                    <td class="ogi-num"><?= Html::encode($indicador['naturaleza']) ?></td>
                    <td class="ogi-num"><?= Html::encode((string)$indicador['t1']) ?></td>
                    <td class="ogi-num"><?= Html::encode((string)$indicador['t2']) ?></td>
                    <td class="ogi-num"><?= Html::encode((string)$indicador['t3']) ?></td>
                    <td class="ogi-num"><?= Html::encode((string)$indicador['t4']) ?></td>
                    <td class="ogi-num"><?= Html::encode((string)$indicador['metaAnual']) ?></td>
                    <td class="ogi-money"><?= Html::encode($fmt((float)$indicador['presupuesto'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="ogi-total">
                <td colspan="10" class="ogi-money">TOTAL</td>
                <td class="ogi-money"><?= Html::encode($fmt((float)$grupo['totalPresupuesto'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="ogi-total">
            <td colspan="10" class="ogi-money">TOTAL GENERAL</td>
            <td class="ogi-money"><?= Html::encode($fmt((float)$totalGeneral)) ?></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
