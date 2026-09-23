<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdGasto
 * @property string $CodigoGasto
 * @property string $Descripcion
 * @property string $EntidadTransferencia
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 */
class Gasto extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.Gastos';
    }

    public function rules(): array
    {
        return [
            [['CodigoGasto', 'Descripcion', 'EntidadTransferencia', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdGasto'], 'string', 'max' => 36],
            [['CodigoGasto', 'EntidadTransferencia'], 'string', 'max' => 10],
            [['Descripcion'], 'string', 'max' => 500],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdGasto'], 'unique'],
            [['CodigoGasto'], 'validateUniqueActiva', 'skipOnError' => true],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== Estado::ESTADO_VIGENTE) {
            return;
        }

        $id = $this->IdGasto ?: '00000000-0000-0000-0000-000000000000';
        $exists = self::find()
            ->where([
                'CodigoGasto' => $this->CodigoGasto,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andWhere(['<>', 'IdGasto', $id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El código de gasto ya existe.');
        }
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdGasto' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdGasto',
                'CodigoGasto',
                'Descripcion',
                'EntidadTransferencia',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['CodigoGasto' => SORT_ASC]);
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

    public function getItemsDescatalogados(): ActiveQuery
    {
        return $this->hasMany(ItemDescatalogado::class, ['IdGasto' => 'IdGasto']);
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
