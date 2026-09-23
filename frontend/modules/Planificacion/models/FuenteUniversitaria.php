<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdFuenteUniversitaria
 * @property string $Codigo
 * @property string $Descripcion
 * @property string $Nombre_Corto
 * @property string $IdPartida
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Partida $partida
 * @property Item[] $items
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class FuenteUniversitaria extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.FuentesUniversitarias';
    }

    public function rules(): array
    {
        return [
            [['Codigo', 'Descripcion', 'Nombre_Corto', 'IdPartida', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdFuenteUniversitaria', 'IdPartida'], 'string', 'max' => 36],
            [['Codigo'], 'string', 'max' => 10],
            [['Descripcion', 'Nombre_Corto'], 'string', 'max' => 500],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdFuenteUniversitaria'], 'unique'],
            [['IdPartida'], 'exist', 'skipOnError' => true, 'targetClass' => Partida::class, 'targetAttribute' => ['IdPartida' => 'IdPartida']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdFuenteUniversitaria' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdFuenteUniversitaria',
                'Codigo',
                'Descripcion',
                'Nombre_Corto',
                'IdPartida',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['Codigo' => SORT_ASC]);
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

    public function getPartida(): ActiveQuery
    {
        return $this->hasOne(Partida::class, ['IdPartida' => 'IdPartida']);
    }

    public function getItems(): ActiveQuery
    {
        return $this->hasMany(Item::class, ['IdFuenteUniversitaria' => 'IdFuenteUniversitaria']);
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
