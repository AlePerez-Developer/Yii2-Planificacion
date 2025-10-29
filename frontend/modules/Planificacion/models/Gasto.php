<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "Gastos".
 *
 * @property int $CodigoGasto
 * @property string $Descripcion
 * @property string $EntidadTransferencia
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class Gasto extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'Gastos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['Descripcion', 'EntidadTransferencia', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoGasto'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 450],
            [['EntidadTransferencia'], 'string', 'max' => 5],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoGasto'], 'unique'],
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
            'CodigoGasto' => 'Codigo Gasto',
            'Descripcion' => 'Descripcion',
            'EntidadTransferencia' => 'Entidad Transferencia',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Busca un gasto específico por código, excluyendo eliminados
     *
     * @param int $codigo
     * @return Gasto|null
     */
    public static function listOne($codigo): ?Gasto
    {
        return self::find()
            ->where(['CodigoGasto' => $codigo])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    /**
     * Obtiene todos los gastos activos (no eliminados)
     *
     * @return ActiveQuery
     */
    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'CodigoGasto',
                'Descripcion',
                'EntidadTransferencia',
                'CodigoEstado',
                'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['CodigoGasto' => SORT_ASC]);
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
    public function eliminarGasto(): void
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
     * Verifica si existe un gasto con la misma descripción
     *
     * @return bool
     */
    public function exist(): bool
    {
        $gasto = self::find()
            ->where(['Descripcion' => $this->Descripcion])
            ->andWhere(['!=', 'CodigoGasto', $this->CodigoGasto])
            ->andWhere(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->exists();
        
        return $gasto;
    }

    /**
     * Verifica si el gasto está siendo usado en otras tablas
     *
     * @return bool
     */
    public function enUso(): bool
    {
        return false;
    }
}