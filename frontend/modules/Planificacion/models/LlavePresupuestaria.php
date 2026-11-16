<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;
use yii\db\Expression;

/**
 * This is the model class for table "LlavesPresupuestarias".
 *
 * @property string $IdLlavePresupuestaria
 * @property string $IdUnidadEjecutora
 * @property string $IdProyecto
 * @property string $IdActividad
 * @property string $Llave
 * @property int $esOrganizacional
 * @property string $FechaInicio
 * @property string|null $FechaFin
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property UnidadEjecutora $unidadEjecutora
 * @property Proyecto $proyecto
 * @property Actividad $actividad
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property ProgramacionIndicadorGestion[] $programacionesIndicadoresGestiones
 *
 */
class LlavePresupuestaria extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'LlavesPresupuestarias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdLlavePresupuestaria', 'IdUnidadEjecutora', 'IdProyecto', 'IdActividad'], 'string', 'max' => 36],
            [['IdUnidadEjecutora', 'IdProyecto', 'IdActividad', 'Llave', 'esOrganizacional', 'FechaInicio', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['esOrganizacional'], 'integer'],
            [['FechaInicio', 'FechaFin', 'FechaHoraRegistro'], 'safe'],
            [['Llave'], 'string', 'max' => 200],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdLlavePresupuestaria'], 'unique'],
            [['IdUnidadEjecutora'], 'exist', 'skipOnError' => true, 'targetClass' => UnidadEjecutora::class, 'targetAttribute' => ['IdUnidadEjecutora' => 'IdUnidadEjecutora']],
            [['IdProyecto'], 'exist', 'skipOnError' => true, 'targetClass' => Proyecto::class, 'targetAttribute' => ['IdProyecto' => 'IdProyecto']],
            [['IdActividad'], 'exist', 'skipOnError' => true, 'targetClass' => Actividad::class, 'targetAttribute' => ['IdActividad' => 'IdActividad']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdLlavePresupuestaria' => 'Id Llave',
            'IdUnidadEjecutora' => 'Id Unidad',
            'IdProyecto' => 'Id Proyecto',
            'IdActividad' => 'Id Actividad',
            'Llave' => 'Llave',
            'esOrganizacional' => 'Es Organizacional',
            'FechaInicio' => 'Fecha Inicio',
            'FechaFin' => 'Fecha Fin',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->alias('LP')
            ->select([
                'LP.IdLlavePresupuestaria',
                'LP.Llave',
                'LP.esOrganizacional',
                'LP.FechaInicio',
                'LP.FechaFin',
                'Un.IdUnidadEjecutora',
                'Un.Ue',
                'Un.Descripcion as UnidadDescripcion',
                'Da.Da',
                new Expression("CONCAT(Da.Da, '-', Un.Ue) AS [[Compuesto]]"),
                'Pr.IdPrograma',
                'Py.IdProyecto',
                'Ac.IdActividad',
                'LP.CodigoEstado',
                'LP.CodigoUsuario'
            ])
            ->joinWith('unidadEjecutora Un', true, 'INNER JOIN')
            ->joinWith('unidadEjecutora.das Da', true, 'INNER JOIN')
            ->joinWith('proyecto.programa Pr', true, 'INNER JOIN')
            ->joinWith('proyecto Py', true, 'INNER JOIN')
            ->joinWith('actividad Ac', true, 'INNER JOIN')
            ->where(['!=', 'LP.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'Un.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy([
                'Da.Da' => SORT_ASC,
                'Un.Ue' => SORT_ASC,
                'LP.Llave' => SORT_ASC,
            ]);
    }

    public static function listOne(string $id): ?LlavePresupuestaria
    {
        return self::findOne(['IdLlavePresupuestaria' => $id, ['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO]]);
    }

    public static function listOneComplete(string $id): array|ActiveRecord
    {
        $modelo = self::find()->alias('Ll')
            ->joinWith('proyecto.programa Pr', true, 'INNER JOIN')
            ->where(['IdLlavePresupuestaria' => $id])
            ->andWhere(['!=', 'Ll.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        return [
            'IdUnidadEjecutora' => $modelo['IdUnidadEjecutora'],
            'IdPrograma' => $modelo->proyecto->programa->IdPrograma ?? null,
            'IdProyecto' => $modelo['IdProyecto'],
            'IdActividad' => $modelo['IdActividad'],
            'esOrganizacional' => $modelo['esOrganizacional'],
            'FechaInicio' => $modelo['FechaInicio'],
        ];
    }

    /**
     * Lista llaves para programación anual, restringidas a DAs asignadas al indicador.
     */
    public static function listAllbyProgramacion(string $idIndicadorEstrategico, string $idGestion): array
    {
        return self::find()
            ->alias('lp')
            ->select([
                'lp.IdLlavePresupuestaria',
                'lp.Llave',
                'Descripcion' => new Expression("CONCAT(Da.Da, '-', Un.Ue, ' ', Un.Descripcion)"),
                'Da.Da',
                'Da.IdDa',
                'Estado' => new Expression("
                    CASE
                        WHEN pig.IdProgramacionIndicadorGestion IS NOT NULL
                        THEN 1
                        ELSE 0
                    END
                "),
            ])
            ->innerJoin(
                ['Un' => UnidadEjecutora::tableName()],
                'Un.IdUnidadEjecutora = lp.IdUnidadEjecutora AND Un.CodigoEstado <> :estadoEliminado',
                [':estadoEliminado' => Estado::ESTADO_ELIMINADO]
            )
            ->innerJoin(
                ['Da' => Da::tableName()],
                'Da.IdDa = Un.IdDa AND Da.CodigoEstado <> :estadoEliminadoDa',
                [':estadoEliminadoDa' => Estado::ESTADO_ELIMINADO]
            )
            ->innerJoin(
                ['pida' => ProgramacionIndicadorDa::tableName()],
                'pida.IdDa = Un.IdDa AND pida.IdIndicador = :idIndicadorDa',
                [':idIndicadorDa' => $idIndicadorEstrategico]
            )
            ->leftJoin(
                ['pig' => ProgramacionIndicadorGestion::tableName()],
                'pig.IdLlavePresupuestaria = lp.IdLlavePresupuestaria
                    AND pig.IdIndicadorEstrategico = :idIndicador
                    AND pig.IdGestion = :idGestion',
                [
                    ':idIndicador' => $idIndicadorEstrategico,
                    ':idGestion' => $idGestion,
                ]
            )
            ->where(['!=', 'lp.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['Da.Da' => SORT_ASC, 'lp.Llave' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * Alterna el estado del modelo V/C, o fuerza un estado si se envía.
     *
     * @param string|null $codigoEstado
     * @return void
     */
    public function cambiarEstado(?string $codigoEstado = null): void
    {
        if ($codigoEstado === Estado::ESTADO_VIGENTE || $codigoEstado === Estado::ESTADO_CADUCO) {
            $this->CodigoEstado = $codigoEstado;
            return;
        }

        $this->CodigoEstado = $this->CodigoEstado == Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
    }

    /**
     * realiza el soft delete de un registro.
     *
     * @return void
     */
    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }

    /**
     * realiza el soft delete de un registro.
     *
     * @return void
     */
    public function finalizar(): void
    {
        $this->FechaFin = date('d-m-Y H:i:s');
    }

    /**
     * Gets query for [[IdUnidad]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUnidadEjecutora(): ActiveQuery
    {
        return $this->hasOne(UnidadEjecutora::class, ['IdUnidadEjecutora' => 'IdUnidadEjecutora']);
    }

    /**
     * Gets query for [[IdProyecto]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getProyecto(): ActiveQuery
    {
        return $this->hasOne(Proyecto::class, ['IdProyecto' => 'IdProyecto']);
    }

    /**
     * Gets a query for [[IdActividad]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getActividad(): ActiveQuery
    {
        return $this->hasOne(Actividad::class, ['IdActividad' => 'IdActividad']);
    }

    /**
     * Gets a query for [[CodigoEstado]].
     *
     * @return ActiveQuery
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets a query for [[CodigoUsuario]].
     *
     * @return ActiveQuery
     */
    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[ProgramacionesIndicadoresGestiones]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getProgramacionesIndicadoresGestiones(): ActiveQuery
    {
        return $this->hasMany(ProgramacionIndicadorGestion::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }
}
