<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use common\models\seguridad\EstadosPoa;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class Ingreso extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Ingresos';
    }

    public function rules(): array
    {
        return [
            [['Descripcion'], 'default', 'value' => null],
            [['IdUnidadEjecutora', 'IdEstadoPoa', 'IdGestion', 'Cantidad', 'Precio', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdIngreso', 'IdUnidadEjecutora', 'IdEstadoPoa', 'IdGestion'], 'string', 'max' => 36],
            [['Cantidad'], 'integer', 'min' => 1],
            [['Precio'], 'number', 'min' => 0.01],
            [['Precio'], 'match', 'pattern' => '/^\d+(?:\.\d{1,2})?$/', 'message' => 'El precio debe tener máximo dos decimales.'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdIngreso'], 'unique'],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdEstadoPoa'], 'exist', 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
            [['IdUnidadEjecutora'], 'exist', 'targetClass' => UnidadEjecutora::class, 'targetAttribute' => ['IdUnidadEjecutora' => 'IdUnidadEjecutora']],
        ];
    }

    public static function listAll(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): ActiveQuery {
        return self::find()->alias('I')
            ->where([
                'I.IdUnidadEjecutora' => $idUnidadEjecutora,
                'I.IdGestion' => $idGestion,
                'I.IdEstadoPoa' => $idEstadoPoa,
            ])
            ->andWhere(['<>', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO]);
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

    public function getUnidadEjecutora(): ActiveQuery
    {
        return $this->hasOne(UnidadEjecutora::class, ['IdUnidadEjecutora' => 'IdUnidadEjecutora']);
    }

    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getEstadoPoa(): ActiveQuery
    {
        return $this->hasOne(EstadosPoa::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
    }
}
