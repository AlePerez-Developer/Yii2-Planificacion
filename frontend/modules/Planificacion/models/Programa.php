<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "Programas".
 *
 * @property int $CodigoPrograma
 * @property string $Codigo
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
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
            [['CodigoPrograma', 'Codigo', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoPrograma'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'string', 'max' => 20],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoPrograma'], 'unique'],
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
            'CodigoPrograma' => 'Codigo Programa',
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
     * @param int $codigo
     * @return Programa|null
     */
    public static function listOne($codigo): ?Programa
    {
        return self::find()
            ->where(['CodigoPrograma' => $codigo])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
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
                'CodigoPrograma',
                'Codigo',
                'Descripcion',
                'CodigoEstado',
                'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['CodigoPrograma' => SORT_ASC]);
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
     * Realiza el soft delete de un registro.
     *
     * @return void
     */
    public function eliminarPrograma(): void
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
     * Verifica si existe un programa con el mismo código
     *
     * @return bool
     */
    public function exist(): bool
    {
        return self::find()
            ->where(['Codigo' => $this->Codigo])
            ->andWhere(['!=', 'CodigoPrograma', $this->CodigoPrograma])
            ->andWhere(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->exists();
    }

    /**
     * Verifica si el programa está siendo usado en otras tablas
     *
     * @return bool
     */
    public function enUso(): bool
    {
        return false;
    }
}