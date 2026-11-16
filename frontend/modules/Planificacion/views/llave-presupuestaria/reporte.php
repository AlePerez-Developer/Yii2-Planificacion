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

    $idUe = (string)($row['IdUnidadEjecutora'] ?? '');
    if ($idUe === '') {
        $idUe = 'sin-ue';
    }

    $unidad = $row['unidadEjecutora'] ?? [];
    $da = $unidad['das'] ?? [];
    $compuesto = (string)(($row['Compuesto'] ?? '')
        ?: ((($da['Da'] ?? $row['Da'] ?? '') . '-' . ($unidad['Ue'] ?? $row['Ue'] ?? ''))));
    $descripcionUe = (string)(($unidad['Descripcion'] ?? '') ?: ($row['UnidadDescripcion'] ?? 'Sin descripción'));

    if (!isset($grupos[$idUe])) {
        $grupos[$idUe] = [
            'compuesto' => $compuesto,
            'descripcion' => $descripcionUe,
            'items' => [],
        ];
    }
    $grupos[$idUe]['items'][] = $row;
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
    .lp-title {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .lp-subtitle {
        margin: 0 0 12px 0;
        font-size: 10px;
        color: #64748b;
    }
    .lp-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    .lp-summary td {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 6px 10px;
        font-size: 9px;
        color: #475569;
        width: 33.33%;
    }
    .lp-summary strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-top: 2px;
    }
    .lp-grupo {
        margin: 0 0 14px 0;
        page-break-inside: avoid;
    }
    .lp-grupo-header {
        background: #0369a1;
        color: #fff;
        padding: 7px 10px;
        border: 1px solid #0369a1;
        border-radius: 4px 4px 0 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .lp-grupo-codigo {
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
    .lp-grupo-meta {
        float: right;
        font-size: 9px;
        font-weight: 600;
        opacity: .9;
    }
    .lp-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 2px;
    }
    .lp-table thead th {
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
    .lp-table tbody td {
        font-size: 9px;
        color: #1e293b;
        padding: 5px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .lp-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .lp-num { width: 6%; text-align: center; color: #64748b; font-weight: 700; }
    .lp-code { width: 28%; font-family: monospace; font-weight: 700; color: #0369a1; text-align: center; }
    .lp-prog { width: 36%; }
    .lp-estado { width: 15%; text-align: center; }
    .lp-org { width: 15%; text-align: center; }
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
    .lp-empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 18px 8px;
        font-size: 10px;
        border: 1px solid #e2e8f0;
    }
</style>

<p class="lp-title">Llaves presupuestarias</p>
<p class="lp-subtitle">Listado ejecutivo agrupado por unidad ejecutora · <?= Html::encode(date('d/m/Y')) ?></p>

<table class="lp-summary">
    <tr>
        <td>Total registradas<strong><?= $total ?></strong></td>
        <td>Vigentes<strong><?= $vigentes ?></strong></td>
        <td>Caducos<strong><?= $caducos ?></strong></td>
    </tr>
</table>

<?php if (empty($grupos)): ?>
    <div class="lp-empty">No hay llaves presupuestarias registradas</div>
<?php else: ?>
    <?php foreach ($grupos as $grupo): ?>
        <div class="lp-grupo">
            <div class="lp-grupo-header">
                <span class="lp-grupo-codigo"><?= Html::encode($grupo['compuesto']) ?></span>
                <?= Html::encode($grupo['descripcion']) ?>
                <span class="lp-grupo-meta"><?= count($grupo['items']) ?> registro(s)</span>
            </div>
            <table class="lp-table">
                <thead>
                <tr>
                    <th class="lp-num">#</th>
                    <th class="lp-code">Llave</th>
                    <th class="lp-prog">Programa / Proyecto / Actividad</th>
                    <th class="lp-org">Organizacional</th>
                    <th class="lp-estado">Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($grupo['items'] as $i => $row): ?>
                    <?php
                    $vigente = ($row['CodigoEstado'] ?? '') === Estado::ESTADO_VIGENTE;
                    $estadoTexto = $vigente ? 'Vigente' : 'Caduco';
                    $estadoClase = $vigente ? 'badge-vigente' : 'badge-caduco';
                    $programa = $row['proyecto']['programa'] ?? [];
                    $proyecto = $row['proyecto'] ?? [];
                    $actividad = $row['actividad'] ?? [];
                    $detalle = trim(
                        (($programa['Codigo'] ?? '') ? 'P ' . $programa['Codigo'] : '')
                        . (($proyecto['Codigo'] ?? '') ? ' · Py ' . $proyecto['Codigo'] : '')
                        . (($actividad['Codigo'] ?? '') ? ' · A ' . $actividad['Codigo'] : '')
                    );
                    $org = (($row['esOrganizacional'] ?? 0) == 1) ? 'Sí' : 'No';
                    ?>
                    <tr>
                        <td class="lp-num"><?= $i + 1 ?></td>
                        <td class="lp-code"><?= Html::encode($row['Llave'] ?? '') ?></td>
                        <td class="lp-prog"><?= Html::encode($detalle) ?></td>
                        <td class="lp-org"><?= $org ?></td>
                        <td class="lp-estado">
                            <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
