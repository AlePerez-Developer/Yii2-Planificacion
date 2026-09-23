<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use common\models\seguridad\EstadosPoa;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdItem_Gestion_Operacion
 * @property string $IdItem_Gestion
 * @property string $IdOperacion
 * @property string $IdEstadoPoa
 * @property float $Cantidad
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ItemGestion $itemGestion
 * @property Operacion $operacion
 * @property EstadosPoa $estadoPoa
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class ItemGestionOperacion extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.Items_Gestiones_Operaciones';
    }

    public function rules(): array
    {
        return [
            [['IdItem_Gestion', 'IdOperacion', 'IdEstadoPoa', 'Cantidad', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdItem_Gestion_Operacion', 'IdItem_Gestion', 'IdOperacion', 'IdEstadoPoa'], 'string', 'max' => 36],
            [['Cantidad'], 'number', 'min' => 0],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdItem_Gestion_Operacion'], 'unique'],
            [
                ['IdItem_Gestion', 'IdOperacion', 'IdEstadoPoa'],
                'unique',
                'targetAttribute' => ['IdItem_Gestion', 'IdOperacion', 'IdEstadoPoa'],
            ],
            [['IdItem_Gestion'], 'exist', 'skipOnError' => true, 'targetClass' => ItemGestion::class, 'targetAttribute' => ['IdItem_Gestion' => 'IdItem_Gestion']],
            [['IdOperacion'], 'exist', 'skipOnError' => true, 'targetClass' => Operacion::class, 'targetAttribute' => ['IdOperacion' => 'IdOperacion']],
            [['IdEstadoPoa'], 'exist', 'skipOnError' => true, 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdItem_Gestion_Operacion' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdItem_Gestion_Operacion',
                'IdItem_Gestion',
                'IdOperacion',
                'IdEstadoPoa',
                'Cantidad',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO]);
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

    public function getItemGestion(): ActiveQuery
    {
        return $this->hasOne(ItemGestion::class, ['IdItem_Gestion' => 'IdItem_Gestion']);
    }

    public function getOperacion(): ActiveQuery
    {
        return $this->hasOne(Operacion::class, ['IdOperacion' => 'IdOperacion']);
    }

    public function getEstadoPoa(): ActiveQuery
    {
        return $this->hasOne(EstadosPoa::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
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
