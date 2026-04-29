<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "LlavePresupuestaria".
 *
 * @property string $IdLlavePresupuestaria
 * @property string $IdUnidad
 * @property string $IdPrograma
 * @property string $IdProyecto
 * @property string $IdActividad
 * @property string $Descripcion
 * @property float $TechoPresupuestario
 * @property string $FechaInicio
 * @property string|null $FechaFin
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Unidad $unidad
 * @property Programa $programa
 * @property Proyecto $proyecto
 * @property Actividad $actividad
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class LlavePresupuestaria extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'LlavesPresupuestarias';
    }

    public function rules(): array
    {
        return [
            [['IdUnidad', 'IdPrograma', 'IdProyecto', 'IdActividad', 'Descripcion', 'TechoPresupuestario', 'FechaInicio', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdLlavePresupuestaria', 'IdUnidad', 'IdPrograma', 'IdProyecto', 'IdActividad'], 'string', 'max' => 250],
            [['TechoPresupuestario'], 'number'],
            [['FechaInicio', 'FechaFin', 'FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Descripcion'], 'trim'],
            [
                ['CodigoEstado'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Estado::class,
                'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']
            ],
            [
                ['CodigoUsuario'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Usuario::class,
                'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']
            ],
            [
                ['IdUnidad'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Unidad::class,
                'targetAttribute' => ['IdUnidad' => 'IdUnidad']
            ],
            [
                ['IdPrograma'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Programa::class,
                'targetAttribute' => ['IdPrograma' => 'IdPrograma']
            ],
            [
                ['IdProyecto'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Proyecto::class,
                'targetAttribute' => ['IdProyecto' => 'IdProyecto']
            ],
            [
                ['IdActividad'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Actividad::class,
                'targetAttribute' => ['IdActividad' => 'IdActividad']
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'IdUnidad' => 'Unidad',
            'IdPrograma' => 'Programa',
            'IdProyecto' => 'Proyecto',
            'IdActividad' => 'Actividad',
            'Descripcion' => 'Descripción',
            'TechoPresupuestario' => 'Techo Presupuestario',
            'FechaInicio' => 'Fecha Inicio',
            'FechaFin' => 'Fecha Fin',
            'CodigoEstado' => 'Estado',
            'FechaHoraRegistro' => 'Fecha Registro',
            'CodigoUsuario' => 'Usuario',
        ];
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->alias('LP')
            ->select([
                'LP.IdLlavePresupuestaria',
                'CONCAT(U.Da,\'-\',U.Ue,\'-\',PR.Codigo,\'-\',PY.Codigo,\'-\',AC.Codigo) AS Llave',
                'U.IdUnidad',
                'PR.IdPrograma',
                'PY.IdProyecto',
                'AC.IdActividad',
                'LP.Descripcion',
                'LP.TechoPresupuestario',
                'LP.FechaInicio',
                'LP.FechaFin',
                'LP.CodigoEstado',
                'LP.CodigoUsuario'

            ])
            ->joinWith('unidad U', true, 'INNER JOIN')
            ->joinWith('programa PR', true, 'INNER JOIN')
            ->joinWith('proyecto PY', true, 'INNER JOIN')
            ->joinWith('actividad AC', true, 'INNER JOIN')
            ->where(['!=', 'LP.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy([
                'LP.IdUnidad' => SORT_ASC,
                'LP.IdPrograma' => SORT_ASC,
                'LP.IdProyecto' => SORT_ASC,
                'LP.IdActividad' => SORT_ASC,
            ]);
    }

    public static function listOne(string $id): ?LlavePresupuestaria
    {
        return self::findOne(['IdLlavePresupuestaria' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    /**
     * alterna el estado del modelo V/C.
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
        $this->FechaFin =  date('d-m-Y H:i:s');
    }

    /**
     * Gets a query for [[Unidades]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUnidad(): ActiveQuery
    {
        return $this->hasOne(Unidad::class, ['IdUnidad' => 'IdUnidad']);
    }

    /**
     * Gets a query for [[Programas]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getPrograma(): ActiveQuery
    {
        return $this->hasOne(Programa::class, ['IdPrograma' => 'IdPrograma']);
    }

    /**
     * Gets a query for [[Proyectos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getProyecto(): ActiveQuery
    {
        return $this->hasOne(Proyecto::class, ['IdProyecto' => 'IdProyecto']);
    }

    /**
     * Gets a query for [[Actividades]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getActividad(): ActiveQuery
    {
        return $this->hasOne(Actividad::class, ['IdActividad' => 'IdActividad']);
    }

    /**
     * Gets a query for [[Estados]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets a query for [[Usuarios]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }
}
