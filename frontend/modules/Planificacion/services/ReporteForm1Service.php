<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\models\AreaEstrategica;
use app\modules\Planificacion\models\CatCategoriaIndicador;
use app\modules\Planificacion\models\CatTipoResultado;
use app\modules\Planificacion\models\CatUnidadIndicador;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\ItemCatalogado;
use app\modules\Planificacion\models\ItemDescatalogado;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\models\ObjetivoInstitucional;
use app\modules\Planificacion\models\Operacion;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\PoliticaEstrategica;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaTrimestre;
use app\modules\Planificacion\models\ProgramacionIndicadorTrimestre;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use yii\db\Expression;
use yii\db\Query;

class ReporteForm1Service
{
    public function listarParaReporte(
        string $idGestion,
        string $idUnidadEjecutora,
        string $idEstadoPoa
    ): array {
        $gestion = PeiGestion::findOne($idGestion);
        $unidad = UnidadEjecutora::find()
            ->where(['IdUnidadEjecutora' => $idUnidadEjecutora])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
        $idDa = (string)($unidad?->IdDa ?? '');

        $filas = array_merge(
            $this->listarIndicadoresPoa($idGestion, $idUnidadEjecutora, $idDa),
            $this->listarIndicadoresEstrategicos($idGestion, $idUnidadEjecutora, $idDa)
        );
        $presupuestos = $this->listarPresupuestos($idGestion, $idUnidadEjecutora, $idEstadoPoa);

        $grupos = [];
        foreach ($filas as $fila) {
            $idOgi = (string)$fila['IdObjInstitucional'];
            $idIndicador = (string)$fila['IdIndicador'];
            $clavePresupuesto = $idOgi . '|' . $idIndicador;

            if (!isset($grupos[$idOgi])) {
                $grupos[$idOgi] = [
                    'codArticulacion' => (string)$fila['CodArticulacion'],
                    'objetivo' => (string)$fila['OgiObjetivo'],
                    'indicadores' => [],
                    'totalPresupuesto' => 0.0,
                ];
            }

            if (isset($grupos[$idOgi]['indicadores'][$idIndicador])) {
                $existente = &$grupos[$idOgi]['indicadores'][$idIndicador];
                $existente['t1'] += (int)$fila['T1'];
                $existente['t2'] += (int)$fila['T2'];
                $existente['t3'] += (int)$fila['T3'];
                $existente['t4'] += (int)$fila['T4'];
                $existente['metaAnual'] += (int)$fila['MetaAnual'];
                unset($existente);
                continue;
            }

            $presupuesto = (float)($presupuestos[$clavePresupuesto] ?? 0);
            $grupos[$idOgi]['indicadores'][$idIndicador] = [
                'codigo' => (int)$fila['IndicadorCodigo'],
                'denominacion' => (string)$fila['Denominacion'],
                'tipo' => (string)($fila['Tipo'] ?? ''),
                'categoria' => (string)($fila['Categoria'] ?? ''),
                'naturaleza' => (string)($fila['Naturaleza'] ?? ''),
                'tipoIndicador' => (string)$fila['TipoIndicador'],
                't1' => (int)$fila['T1'],
                't2' => (int)$fila['T2'],
                't3' => (int)$fila['T3'],
                't4' => (int)$fila['T4'],
                'metaAnual' => (int)$fila['MetaAnual'],
                'presupuesto' => $presupuesto,
            ];
            $grupos[$idOgi]['totalPresupuesto'] += $presupuesto;
            unset($presupuestos[$clavePresupuesto]);
        }

        foreach ($grupos as &$grupo) {
            $indicadores = array_values($grupo['indicadores']);
            usort(
                $indicadores,
                static fn(array $a, array $b): int => $a['codigo'] <=> $b['codigo']
            );
            $grupo['indicadores'] = $indicadores;
        }
        unset($grupo);

        uasort(
            $grupos,
            static fn(array $a, array $b): int => strcmp($a['codArticulacion'], $b['codArticulacion'])
        );

        $totalGeneral = array_sum(array_column($grupos, 'totalPresupuesto'));

        return [
            'gestion' => (int)($gestion?->Gestion ?? 0),
            'unidad' => (string)($unidad?->Descripcion ?? ''),
            'grupos' => array_values($grupos),
            'totalGeneral' => $totalGeneral,
        ];
    }

    private function listarIndicadoresPoa(
        string $idGestion,
        string $idUnidadEjecutora,
        string $idDa
    ): array {
        $query = (new Query())
            ->select([
                'IdObjInstitucional' => 'OI.IdObjInstitucional',
                'CodArticulacion' => new Expression('CONCAT(A.Codigo, P.Codigo, OE.Codigo, OI.Codigo)'),
                'OgiObjetivo' => 'OI.Objetivo',
                'IdIndicador' => 'IP.IdIndicador',
                'IndicadorCodigo' => 'IP.Codigo',
                'Denominacion' => 'I.Descripcion',
                'Tipo' => 'TR.Descripcion',
                'Categoria' => 'CI.Descripcion',
                'Naturaleza' => 'UI.Descripcion',
                'TipoIndicador' => new Expression("'POA'"),
                'MetaAnual' => new Expression('SUM(COALESCE(PG.MetaProgramada, 0))'),
                'T1' => new Expression('SUM(COALESCE(PT.MetaPrimerTrimestre, 0))'),
                'T2' => new Expression('SUM(COALESCE(PT.MetaSegundoTrimestre, 0))'),
                'T3' => new Expression('SUM(COALESCE(PT.MetaTercerTrimestre, 0))'),
                'T4' => new Expression('SUM(COALESCE(PT.MetaCuartoTrimestre, 0))'),
            ])
            ->from(['OI' => ObjetivoInstitucional::tableName()])
            ->innerJoin(['OE' => ObjetivoEstrategico::tableName()], 'OE.IdObjEstrategico = OI.IdObjEstrategico')
            ->innerJoin(['A' => AreaEstrategica::tableName()], 'A.IdAreaEstrategica = OE.IdAreaEstrategica')
            ->innerJoin(['P' => PoliticaEstrategica::tableName()], 'P.IdPoliticaEstrategica = OE.IdPoliticaEstrategica')
            ->innerJoin(['OES' => ObjetivoEspecifico::tableName()], 'OES.IdObjInstitucional = OI.IdObjInstitucional')
            ->innerJoin(
                ['PG' => ProgramacionIndicadorPoaGestion::tableName()],
                'PG.IdObjEspecifico = OES.IdObjEspecifico AND PG.IdGestion = OI.IdGestion'
            )
            ->innerJoin(
                ['LP' => LlavePresupuestaria::tableName()],
                'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria'
            )
            ->innerJoin(['IP' => IndicadorPoa::tableName()], 'IP.IdIndicador = PG.IdIndicadorPoa')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = IP.IdIndicador')
            ->leftJoin(['TR' => CatTipoResultado::tableName()], 'TR.IdTipoResultado = I.IdTipoResultado')
            ->leftJoin(['CI' => CatCategoriaIndicador::tableName()], 'CI.IdCategoriaIndicador = I.IdCategoriaIndicador')
            ->leftJoin(['UI' => CatUnidadIndicador::tableName()], 'UI.IdUnidadIndicador = I.IdUnidadIndicador')
            ->leftJoin(
                ['PT' => ProgramacionIndicadorPoaTrimestre::tableName()],
                'PT.IdProgramacionIndicadorPoaGestion = PG.IdProgramacionIndicadorPoaGestion'
            )
            ->where([
                'OI.IdGestion' => $idGestion,
                'OI.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OE.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OES.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'IP.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->groupBy([
                'OI.IdObjInstitucional',
                'A.Codigo',
                'P.Codigo',
                'OE.Codigo',
                'OI.Codigo',
                'OI.Objetivo',
                'IP.IdIndicador',
                'IP.Codigo',
                'I.Descripcion',
                'TR.Descripcion',
                'CI.Descripcion',
                'UI.Descripcion',
            ]);

        if ($idDa !== '') {
            $query->andWhere(['OES.IdDa' => $idDa]);
        }

        return $query->all();
    }

    private function listarIndicadoresEstrategicos(
        string $idGestion,
        string $idUnidadEjecutora,
        string $idDa
    ): array {
        $query = (new Query())
            ->select([
                'IdObjInstitucional' => 'OI.IdObjInstitucional',
                'CodArticulacion' => new Expression('CONCAT(A.Codigo, P.Codigo, OE.Codigo, OI.Codigo)'),
                'OgiObjetivo' => 'OI.Objetivo',
                'IdIndicador' => 'IE.IdIndicador',
                'IndicadorCodigo' => 'IE.Codigo',
                'Denominacion' => 'I.Descripcion',
                'Tipo' => 'TR.Descripcion',
                'Categoria' => 'CI.Descripcion',
                'Naturaleza' => 'UI.Descripcion',
                'TipoIndicador' => new Expression("'Estratégico'"),
                'MetaAnual' => new Expression('SUM(COALESCE(PG.MetaProgramada, 0))'),
                'T1' => new Expression('SUM(COALESCE(PT.MetaPrimerTrimestre, 0))'),
                'T2' => new Expression('SUM(COALESCE(PT.MetaSegundoTrimestre, 0))'),
                'T3' => new Expression('SUM(COALESCE(PT.MetaTercerTrimestre, 0))'),
                'T4' => new Expression('SUM(COALESCE(PT.MetaCuartoTrimestre, 0))'),
            ])
            ->from(['OI' => ObjetivoInstitucional::tableName()])
            ->innerJoin(['OE' => ObjetivoEstrategico::tableName()], 'OE.IdObjEstrategico = OI.IdObjEstrategico')
            ->innerJoin(['A' => AreaEstrategica::tableName()], 'A.IdAreaEstrategica = OE.IdAreaEstrategica')
            ->innerJoin(['P' => PoliticaEstrategica::tableName()], 'P.IdPoliticaEstrategica = OE.IdPoliticaEstrategica')
            ->innerJoin(['OES' => ObjetivoEspecifico::tableName()], 'OES.IdObjInstitucional = OI.IdObjInstitucional')
            ->innerJoin(['OP' => Operacion::tableName()], 'OP.IdObjEspecifico = OES.IdObjEspecifico')
            ->innerJoin(['IE' => IndicadorEstrategico::tableName()], 'IE.IdIndicador = OP.IdIndicador')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = IE.IdIndicador')
            ->innerJoin(
                ['PG' => ProgramacionIndicadorGestion::tableName()],
                'PG.IdIndicadorEstrategico = IE.IdIndicador'
                . ' AND PG.IdLlavePresupuestaria = OP.IdLlavePresupuestaria'
                . ' AND PG.IdGestion = OI.IdGestion'
            )
            ->leftJoin(['TR' => CatTipoResultado::tableName()], 'TR.IdTipoResultado = I.IdTipoResultado')
            ->leftJoin(['CI' => CatCategoriaIndicador::tableName()], 'CI.IdCategoriaIndicador = I.IdCategoriaIndicador')
            ->leftJoin(['UI' => CatUnidadIndicador::tableName()], 'UI.IdUnidadIndicador = I.IdUnidadIndicador')
            ->leftJoin(
                ['PT' => ProgramacionIndicadorTrimestre::tableName()],
                'PT.IdProgramacionIndicadorGestion = PG.IdProgramacionIndicadorGestion'
            )
            ->where([
                'OI.IdGestion' => $idGestion,
                'OI.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OE.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OES.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OP.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'IE.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'OP.IdGestion' => $idGestion,
            ])
            ->groupBy([
                'OI.IdObjInstitucional',
                'A.Codigo',
                'P.Codigo',
                'OE.Codigo',
                'OI.Codigo',
                'OI.Objetivo',
                'IE.IdIndicador',
                'IE.Codigo',
                'I.Descripcion',
                'TR.Descripcion',
                'CI.Descripcion',
                'UI.Descripcion',
            ]);

        if ($idDa !== '') {
            $query->andWhere(['OES.IdDa' => $idDa]);
        }

        return $query->all();
    }

    /**
     * @return array<string, float>
     */
    private function listarPresupuestos(
        string $idGestion,
        string $idUnidadEjecutora,
        string $idEstadoPoa
    ): array {
        $expresion = new Expression(
            'SUM(CAST(IT.cantidad AS decimal(18,2)) * CAST(IT.Precio AS decimal(18,2)))'
        );

        $catalogado = (new Query())
            ->select([
                'IdObjInstitucional' => 'OES.IdObjInstitucional',
                'IdIndicador' => 'OP.IdIndicador',
                'Presupuesto' => $expresion,
            ])
            ->from(['IT' => ItemCatalogado::tableName()])
            ->innerJoin(['OP' => Operacion::tableName()], 'OP.IdOperacion = IT.IdOperacion')
            ->innerJoin(['OES' => ObjetivoEspecifico::tableName()], 'OES.IdObjEspecifico = OP.IdObjEspecifico')
            ->where([
                'IT.IdGestion' => $idGestion,
                'IT.IdEstadoPoa' => $idEstadoPoa,
                'IT.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OP.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'OP.IdGestion' => $idGestion,
            ])
            ->groupBy(['OES.IdObjInstitucional', 'OP.IdIndicador'])
            ->all();

        $descatalogado = (new Query())
            ->select([
                'IdObjInstitucional' => 'OES.IdObjInstitucional',
                'IdIndicador' => 'OP.IdIndicador',
                'Presupuesto' => $expresion,
            ])
            ->from(['IT' => ItemDescatalogado::tableName()])
            ->innerJoin(['OP' => Operacion::tableName()], 'OP.IdOperacion = IT.IdOperacion')
            ->innerJoin(['OES' => ObjetivoEspecifico::tableName()], 'OES.IdObjEspecifico = OP.IdObjEspecifico')
            ->where([
                'IT.IdGestion' => $idGestion,
                'IT.IdEstadoPoa' => $idEstadoPoa,
                'IT.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OP.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'OP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'OP.IdGestion' => $idGestion,
            ])
            ->groupBy(['OES.IdObjInstitucional', 'OP.IdIndicador'])
            ->all();

        $totales = [];
        foreach (array_merge($catalogado, $descatalogado) as $fila) {
            $clave = $fila['IdObjInstitucional'] . '|' . $fila['IdIndicador'];
            $totales[$clave] = ($totales[$clave] ?? 0) + (float)$fila['Presupuesto'];
        }

        return $totales;
    }
}
