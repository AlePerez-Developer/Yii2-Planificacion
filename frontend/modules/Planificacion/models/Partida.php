<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdPartida
 * @property string $IdFuente
 * @property string $IdOrganismo
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 */
class Partida extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.Partidas';
    }

    public function rules(): array
    {
        return [
            [['IdFuente', 'IdOrganismo', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdPartida', 'IdFuente', 'IdOrganismo'], 'string', 'max' => 36],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdPartida'], 'unique'],
            [['IdOrganismo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['IdFuente'], 'exist', 'skipOnError' => true, 'targetClass' => Fuente::class, 'targetAttribute' => ['IdFuente' => 'IdFuente']],
            [['IdOrganismo'], 'exist', 'skipOnError' => true, 'targetClass' => Organismo::class, 'targetAttribute' => ['IdOrganismo' => 'IdOrganismo']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== Estado::ESTADO_VIGENTE) {
            return;
        }

        $id = $this->IdPartida ?: '00000000-0000-0000-0000-000000000000';
        $exists = self::find()
            ->where([
                'IdFuente' => $this->IdFuente,
                'IdOrganismo' => $this->IdOrganismo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andWhere(['<>', 'IdPartida', $id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'Ya existe una partida vigente para esa fuente y organismo.');
        }
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdPartida' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()->alias('P')
            ->select([
                'P.IdPartida',
                'P.IdFuente',
                'P.IdOrganismo',
                'P.CodigoEstado',
                'P.CodigoUsuario',
                'FuenteDescripcion' => 'F.Descripcion',
                'OrganismoDescripcion' => 'O.Descripcion',
            ])
            ->innerJoin(['F' => Fuente::tableName()], 'F.IdFuente = P.IdFuente')
            ->innerJoin(['O' => Organismo::tableName()], 'O.IdOrganismo = P.IdOrganismo')
            ->where(['<>', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['F.Descripcion' => SORT_ASC, 'O.Descripcion' => SORT_ASC]);
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

    public function getFuente(): ActiveQuery
    {
        return $this->hasOne(Fuente::class, ['IdFuente' => 'IdFuente']);
    }

    public function getOrganismo(): ActiveQuery
    {
        return $this->hasOne(Organismo::class, ['IdOrganismo' => 'IdOrganismo']);
    }

    public function getFuentesUniversitarias(): ActiveQuery
    {
        return $this->hasMany(FuenteUniversitaria::class, ['IdPartida' => 'IdPartida']);
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
