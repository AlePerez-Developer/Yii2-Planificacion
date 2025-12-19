<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "Programas".
 *
 * @property string $IdPrograma
 * @property string $Codigo
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property Actividad[] $actividades
 * @property Proyecto[] $proyectos
 * @property LlavePresupuestaria[] $llavesPresupuestarias
 */
class Programa extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'Programas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['Codigo', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdPrograma'], 'string', 'max' => 36],
            [['Codigo'],'match','pattern' => '/^\d{3}$/','message' => 'Debe contener exactamente 3 dígitos (ej: 023).'],
            [['Descripcion'], 'string', 'max' => 500],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdPrograma'], 'unique'],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * Valida que no exista otra política activa con el mismo código y área estratégica.
     *
     * @param string $attribute
     * @used-by rules()
     * @noinspection PhpUnused
     */
    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== 'V') {
            return;
        }

        $id = $this->IdPrograma == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdPrograma;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdPrograma', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de Programa ya existe');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdPrograma' => 'Id Programa',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Busca un programa específico por código, excluyendo eliminados
     *
     * @param string $id
     * @return Programa|null
     */
    public static function listOne(string $id): ?Programa
    {
        return self::findOne(['IdPrograma' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    /**
     * Obtiene todos los programas activos (no eliminados)
     *
     * @return ActiveQuery
     */
    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdPrograma',
                'Codigo',
                'Descripcion',
                'CodigoEstado',
                'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['Codigo' => SORT_ASC]);
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
     * Gets a query for [[Actividades]].
     *
     * @return ActiveQuery
     */
    public function getActividades(): ActiveQuery
    {
        return $this->hasMany(Actividad::class, ['IdPrograma' => 'IdPrograma']);
    }

    /**
     * Gets a query for [[Proyectos]].
     *
     * @return ActiveQuery
     */
    public function getProyectos(): ActiveQuery
    {
        return $this->hasMany(Proyecto::class, ['IdPrograma' => 'IdPrograma']);
    }

    /**
     * Gets a query for [[LlavesPresupuestarias]].
     *
     * @return ActiveQuery
     */
    public function getLlavesPresupuestarias(): ActiveQuery
    {
        return $this->hasMany(LlavePresupuestaria::class, ['IdProyecto' => 'IdProyecto']);
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
}