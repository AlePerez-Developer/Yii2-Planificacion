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
 * @property string $IdDa
 * @property string $IdUe
 * @property string $IdProyecto
 * @property string $IdActividad
 * @property string $Llave
 * @property string $Descripcion
 * @property int $esOrganizacional
 * @property string $FechaInicio
 * @property string|null $FechaFin
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Da $idDa
 * @property Ue $idUe
 * @property Proyecto $idProyecto
 * @property Actividad $idActividad
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
            [['IdLlavePresupuestaria', 'IdDa', 'IdUe', 'IdProyecto', 'IdActividad'], 'string', 'max' => 36],
            [['IdDa', 'IdUe', 'IdProyecto', 'IdActividad', 'Llave', 'Descripcion', 'esOrganizacional', 'FechaInicio', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['esOrganizacional'], 'integer'],
            [['FechaInicio', 'FechaFin', 'FechaHoraRegistro'], 'safe'],
            [['Llave'], 'string', 'max' => 200],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdLlavePresupuestaria'], 'unique'],
            [['IdDa'], 'exist', 'skipOnError' => true, 'targetClass' => Da::class, 'targetAttribute' => ['IdDa' => 'IdDa']],
            [['IdUe'], 'exist', 'skipOnError' => true, 'targetClass' => Ue::class, 'targetAttribute' => ['IdUe' => 'IdUe']],
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
            'IdLlavePresupuestaria' => 'Id Llave Presupuestaria',
            'IdDa' => 'Id Da',
            'IdUe' => 'Id Ue',
            'IdProyecto' => 'Id Proyecto',
            'IdActividad' => 'Id Actividad',
            'Llave' => 'Llave',
            'Descripcion' => 'Descripcion',
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
                'LP.Descripcion',
                'LP.esOrganizacional',
                'LP.FechaInicio',
                'LP.FechaFin',
                'Da.IdDa',
                'Ue.IdUe',
                'Pr.IdPrograma',
                'Py.IdProyecto',
                'Ac.IdActividad',
                'LP.CodigoEstado',
                'LP.CodigoUsuario'
            ])
            ->joinWith('da Da', true, 'INNER JOIN')
            ->joinWith('ue Ue', true, 'INNER JOIN')
            ->joinWith('proyecto.programa Pr', true, 'INNER JOIN')
            ->joinWith('proyecto Py', true, 'INNER JOIN')
            ->joinWith('actividad Ac', true, 'INNER JOIN')
            ->where(['!=', 'LP.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy([
                'Da.Da' => SORT_ASC,
                'Ue.Ue' => SORT_ASC
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
            'IdDa' => $modelo['IdDa'],
            'IdUe' => $modelo['IdUe'],
            'IdPrograma' => $modelo->proyecto->programa->IdPrograma ?? null,
            'IdProyecto' => $modelo['IdProyecto'],
            'IdActividad' => $modelo['IdActividad'],
            'Descripcion' => $modelo['Descripcion'],
            'esOrganizacional' => $modelo['esOrganizacional'],
            'FechaInicio' => $modelo['FechaInicio'],
        ];
    }

    public static function listAllbyProgramacion(string $idIndicadorEstrategico, string $idGestion): array
    {
        return LlavePresupuestaria::find()
            ->alias('lp')
            ->select([
                'lp.IdLlavePresupuestaria',
                'lp.Llave',
                'lp.Descripcion',
                'Estado' => new Expression("
            CASE 
                WHEN pig.IdProgramacionIndicadorGestio IS NOT NULL 
                THEN 1 
                ELSE 0 
            END
        ")
            ])
            ->leftJoin(
                ['pig' => ProgramacionIndicadorGestion::tableName()],
                'pig.IdLlavePresupuestaria = lp.IdLlavePresupuestaria
         AND pig.IdIndicadorEstrategico = :idIndicador
         AND pig.IdGestion = :idGestion',
                [
                    ':idIndicador' => $idIndicadorEstrategico,
                    ':idGestion' => $idGestion
                ]
            )
            ->orderBy(['lp.Llave' => SORT_ASC])
            ->asArray()
            ->all();

    }

    /**
     * Alterna el estado del modelo V/C.
     *
     * @return void
     */
    public function cambiarEstado(): void
    {
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
     * Gets query for [[IdDa]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getDa(): ActiveQuery
    {
        return $this->hasOne(Da::class, ['IdDa' => 'IdDa']);
    }

    /**
     * Gets query for [[IdUe]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUe(): ActiveQuery
    {
        return $this->hasOne(Ue::class, ['IdUe' => 'IdUe']);
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