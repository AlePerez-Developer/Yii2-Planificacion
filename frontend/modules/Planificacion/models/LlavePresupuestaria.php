<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "LlavePresupuestaria".
 *
 * @property int $CodigoUnidad
 * @property int $CodigoPrograma
 * @property int $CodigoProyecto
 * @property int $CodigoActividad
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
        return 'LlavePresupuestaria';
    }

    public static function primaryKey(): array
    {
        return ['CodigoUnidad', 'CodigoPrograma', 'CodigoProyecto', 'CodigoActividad'];
    }

    public function rules(): array
    {
        return [
            [
                ['CodigoUnidad', 'CodigoPrograma', 'CodigoProyecto', 'CodigoActividad', 'Descripcion', 'TechoPresupuestario', 'FechaInicio', 'CodigoEstado', 'CodigoUsuario'],
                'required'
            ],
            [['CodigoUnidad', 'CodigoPrograma', 'CodigoProyecto', 'CodigoActividad'], 'integer'],
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
                ['CodigoUnidad'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Unidad::class,
                'targetAttribute' => ['CodigoUnidad' => 'CodigoUnidad']
            ],
            [
                ['CodigoPrograma'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Programa::class,
                'targetAttribute' => ['CodigoPrograma' => 'CodigoPrograma']
            ],
            [
                ['CodigoProyecto'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Proyecto::class,
                'targetAttribute' => ['CodigoProyecto' => 'CodigoProyecto']
            ],
            [
                ['CodigoActividad'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Actividad::class,
                'targetAttribute' => ['CodigoActividad' => 'CodigoActividad']
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'CodigoUnidad' => 'Unidad',
            'CodigoPrograma' => 'Programa',
            'CodigoProyecto' => 'Proyecto',
            'CodigoActividad' => 'Actividad',
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
                'LP.CodigoUnidad',
                'LP.CodigoPrograma',
                'LP.CodigoProyecto',
                'LP.CodigoActividad',
                'LP.Descripcion',
                'LP.TechoPresupuestario',
                'LP.FechaInicio',
                'LP.FechaFin',
                'LP.CodigoEstado',
                'UnidadDescripcion' => 'U.Descripcion',
                'UnidadDa' => 'U.Da',
                'UnidadUe' => 'U.Ue',
                'ProgramaCodigo' => 'PR.Codigo',
                'ProgramaDescripcion' => 'PR.Descripcion',
                'ProyectoCodigo' => 'PY.Codigo',
                'ProyectoDescripcion' => 'PY.Descripcion',
                'ActividadCodigo' => 'AC.Codigo',
                'ActividadDescripcion' => 'AC.Descripcion',
            ])
            ->leftJoin('Unidades U', 'U.CodigoUnidad = LP.CodigoUnidad')
            ->leftJoin('Programas PR', 'PR.CodigoPrograma = LP.CodigoPrograma')
            ->leftJoin('Proyectos PY', 'PY.CodigoProyecto = LP.CodigoProyecto')
            ->leftJoin('Actividades AC', 'AC.CodigoActividad = LP.CodigoActividad')
            ->where(['!=', 'LP.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy([
                'LP.CodigoUnidad' => SORT_ASC,
                'LP.CodigoPrograma' => SORT_ASC,
                'LP.CodigoProyecto' => SORT_ASC,
                'LP.CodigoActividad' => SORT_ASC,
            ]);
    }

    public static function listOne(int $codigoUnidad, int $codigoPrograma, int $codigoProyecto, int $codigoActividad): ?self
    {
        return self::find()
            ->where([
                'CodigoUnidad' => $codigoUnidad,
                'CodigoPrograma' => $codigoPrograma,
                'CodigoProyecto' => $codigoProyecto,
                'CodigoActividad' => $codigoActividad,
            ])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public function cambiarEstado(): void
    {
        $this->CodigoEstado = $this->CodigoEstado === Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
    }

    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }

    public function finalizar(?string $fechaFin = null): void
    {
        // Validación: La fecha de inicio NO puede ser futura
        $fechaInicioTimestamp = strtotime($this->FechaInicio);
        $hoyTimestamp = strtotime(date('Y-m-d'));
        
        if ($fechaInicioTimestamp > $hoyTimestamp) {
            throw new \yii\base\InvalidCallException(
                'No se puede finalizar una llave cuya fecha de inicio es posterior a la fecha actual. ' .
                'FechaInicio: ' . date('Y-m-d', $fechaInicioTimestamp) . ', ' .
                'Hoy: ' . date('Y-m-d', $hoyTimestamp)
            );
        }
        
        // Si no se proporciona fecha, usar la fecha actual de PHP (no GETDATE() de SQL)
        if ($fechaFin === null) {
            $fechaFin = date('Y-m-d H:i:s');
        }
        
        $this->FechaFin = $fechaFin;
        $this->CodigoEstado = Estado::ESTADO_CADUCO;
    }

    public function exist(): bool
    {
        $query = self::find()
            ->where([
                'CodigoUnidad' => $this->CodigoUnidad,
                'CodigoPrograma' => $this->CodigoPrograma,
                'CodigoProyecto' => $this->CodigoProyecto,
                'CodigoActividad' => $this->CodigoActividad,
            ])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO]);

        if (!$this->getIsNewRecord()) {
            $oldPk = $this->getOldPrimaryKey(true);
            $query->andWhere([
                'not', [
                    'and',
                    ['CodigoUnidad' => $oldPk['CodigoUnidad'] ?? null],
                    ['CodigoPrograma' => $oldPk['CodigoPrograma'] ?? null],
                    ['CodigoProyecto' => $oldPk['CodigoProyecto'] ?? null],
                    ['CodigoActividad' => $oldPk['CodigoActividad'] ?? null],
                ],
            ]);
        }

        return $query->exists();
    }

    public function enUso(): bool
    {
        return false;
    }

    public function getUnidad(): ActiveQuery
    {
        return $this->hasOne(Unidad::class, ['CodigoUnidad' => 'CodigoUnidad']);
    }

    public function getPrograma(): ActiveQuery
    {
        return $this->hasOne(Programa::class, ['CodigoPrograma' => 'CodigoPrograma']);
    }

    public function getProyecto(): ActiveQuery
    {
        return $this->hasOne(Proyecto::class, ['CodigoProyecto' => 'CodigoProyecto']);
    }

    public function getActividad(): ActiveQuery
    {
        return $this->hasOne(Actividad::class, ['CodigoActividad' => 'CodigoActividad']);
    }

    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }
}
