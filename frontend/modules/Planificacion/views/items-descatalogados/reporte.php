<?php

use yii\helpers\Html;

?>
<style>
    .reporte-header { height: 70px; }
    .reporte-footer { font-size: 9px; color: #444; border-top: 1px solid #ccc; padding-top: 4px; }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th, .items-table td { border: 1px solid #bfbfbf; padding: 6px; font-size: 9px; }
    .items-table th { background: #eef3fb; text-align: center; }
    .ops { color: #444; font-size: 8px; margin: 0; }
    .total { text-align: right; font-weight: bold; margin-top: 10px; }
</style>

<h3>Formulario <?= Html::encode((string)$formulario) ?> - Ítems descatalogados</h3>
<table class="items-table">
    <thead>
    <tr>
        <th>Descripción</th>
        <th>Gasto</th>
        <th>Fuente / Organismo</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>Total</th>
        <th>Operaciones</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= Html::encode($item['Descripcion'] ?? '') ?></td>
            <td>
                <?= Html::encode($item['GastoCodigo'] ?? '') ?> -
                <?= Html::encode($item['GastoDescripcion'] ?? '') ?>
            </td>
            <td>
                <?= Html::encode($item['FuenteDescripcion'] ?? '') ?><br>
                <?= Html::encode($item['OrganismoDescripcion'] ?? '') ?>
            </td>
            <td style="text-align:right"><?= number_format((float)($item['CantidadTotal'] ?? 0), 2) ?></td>
            <td style="text-align:right"><?= number_format((float)($item['Precio'] ?? 0), 2) ?></td>
            <td style="text-align:right"><?= number_format((float)($item['TotalItem'] ?? 0), 2) ?></td>
            <td>
                <?php foreach ($item['operaciones'] ?? [] as $op): ?>
                    <p class="ops">
                        <?= Html::encode($op['Codigo'] ?? '') ?> -
                        <?= Html::encode($op['Descripcion'] ?? '') ?>
                        (<?= number_format((float)($op['Cantidad'] ?? 0), 2) ?>)
                    </p>
                <?php endforeach; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p class="total">Total formulario: <?= number_format((float)$totalFormulario, 2) ?></p>
