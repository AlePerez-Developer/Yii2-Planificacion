<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdUnidadMedida
 * @property string $Descripcion
 * @property string $Simbolo
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Item[] $items
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class UnidadMedida extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.UnidadesMedida';
    }

    public function rules(): array
    {
        return [
            [['Descripcion', 'Simbolo', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdUnidadMedida'], 'string', 'max' => 36],
            [['Descripcion'], 'string', 'max' => 500],
            [['Simbolo'], 'string', 'max' => 10],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdUnidadMedida'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdUnidadMedida' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdUnidadMedida',
                'Descripcion',
                'Simbolo',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['Descripcion' => SORT_ASC]);
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

    public function getItems(): ActiveQuery
    {
        return $this->hasMany(Item::class, ['IdUnidadMedida' => 'IdUnidadMedida']);
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
