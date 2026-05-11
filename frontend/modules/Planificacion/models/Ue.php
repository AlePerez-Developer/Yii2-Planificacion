<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "Ues".
 *
 * @property string $IdUe
 * @property string|null $Ue
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property LlavePresupuestaria[] $llavesPresupuestarias
 */
class Ue extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'Ues';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUe'], 'string'],
            [['Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['Ue'], 'string', 'max' => 3],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdUe'], 'unique'],
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

        $id = $this->IdUe == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdUe;

        $exists = self::find()
            ->where([
                'Ue' => $this->Ue,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdUe', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'La unidad ya existe');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdUe' => 'Id Ue',
            'Ue' => 'Ue',
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
    public static function listOne(string $id): ?Ue
    {
        return self::findOne(['IdUe' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    /**
     * Obtiene todos los programas activos (no eliminados)
     *
     * @param string $search
     * @return ActiveQuery
     */
    public static function listAll(string $search = '%%'): ActiveQuery
    {
        return self::find()
            ->select([
                'IdUe',
                'Ue',
                'Descripcion',
                'CodigoEstado',
                'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'Descripcion', $search, false])
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
     * Gets query for [[LlavesPresupuestarias]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getLlavesPresupuestarias(): ActiveQuery
    {
        return $this->hasMany(LlavePresupuestaria::class, ['IdUe' => 'IdUe']);
    }
}
