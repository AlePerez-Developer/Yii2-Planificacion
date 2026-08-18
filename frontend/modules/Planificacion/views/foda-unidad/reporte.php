<?php

use yii\helpers\Html;

$estilos = [
    'Fortaleza' => ['#155724', '#d4edda'],
    'Debilidad' => ['#721c24', '#f8d7da'],
    'Oportunidad' => ['#004085', '#cce5ff'],
    'Amenaza' => ['#856404', '#fff3cd'],
];
?>
<style>
    .reporte-header {
        height: 70px;
        border-bottom: 1px solid #ffffff;
    }
    .reporte-footer {
        font-size: 9px;
        color: #444;
        border-top: 1px solid #ccc;
        padding-top: 4px;
    }
    .foda-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .foda-table th {
        font-size: 11px;
        text-align: center;
        padding: 8px 4px;
        border: 1px solid #bfbfbf;
    }
    .foda-table td {
        vertical-align: top;
        border: 1px solid #bfbfbf;
        padding: 6px;
        font-size: 9px;
        line-height: 1.35;
    }
    .foda-item {
        margin: 0 0 8px 0;
        text-align: justify;
    }
    .foda-vacio {
        color: #888;
        font-style: italic;
        text-align: center;
        margin-top: 12px;
    }
</style>

<table class="foda-table">
    <thead>
    <tr>
        <?php foreach ($tipos as $tipo): ?>
            <?php [$color, $fondo] = $estilos[$tipo] ?? ['#333', '#f2f2f2']; ?>
            <th style="color: <?= Html::encode($color) ?>; background: <?= Html::encode($fondo) ?>;">
                <?= Html::encode($tipo) ?>
            </th>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <tr>
        <?php foreach ($tipos as $tipo): ?>
            <td>
                <?php if (empty($registros[$tipo])): ?>
                    <p class="foda-vacio">Sin registros vigentes</p>
                <?php else: ?>
                    <?php foreach ($registros[$tipo] as $i => $item): ?>
                        <p class="foda-item">
                            <?= ($i + 1) . '. ' . Html::encode($item['Descripcion'] ?? '') ?>
                        </p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        <?php endforeach; ?>
    </tr>
    </tbody>
</table>
