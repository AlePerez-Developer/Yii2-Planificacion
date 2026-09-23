<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdSigma
 * @property string $Clase
 * @property string $Descripcion
 * @property string $RamaComercial
 * @property string $Especificacion
 * @property string|null $IdGasto
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ItemCatalogado[] $itemsCatalogados
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class CatalogoSigma extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.CatalogoSigma';
    }

    public function rules(): array
    {
        return [
            [['Clase', 'Descripcion', 'RamaComercial', 'Especificacion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdSigma'], 'string', 'max' => 36],
            [['Clase', 'Descripcion', 'RamaComercial', 'Especificacion'], 'string', 'max' => 500],
            [['IdGasto'], 'string', 'max' => 20],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdSigma'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdSigma' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdSigma',
                'Clase',
                'Descripcion',
                'RamaComercial',
                'Especificacion',
                'IdGasto',
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

    public function getItemsCatalogados(): ActiveQuery
    {
        return $this->hasMany(ItemCatalogado::class, ['IdSigma' => 'IdSigma']);
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
