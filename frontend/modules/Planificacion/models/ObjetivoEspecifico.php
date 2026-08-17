<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdObjEspecifico
 * @property string $IdObjInstitucional
 * @property string $IdUnidadEjecutora
 * @property string $IdGestion
 * @property string $Codigo
 * @property string $Objetivo
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ObjetivoInstitucional $objetivosInstitucionales
 * @property UnidadEjecutora $unidadEjecutora
 * @property PeiGestion $peiGestion
 */
class ObjetivoEspecifico extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ObjetivosEspecificos';
    }

    public function rules(): array
    {
        return [
            [['IdObjInstitucional', 'IdUnidadEjecutora', 'IdGestion', 'Codigo', 'Objetivo', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdObjEspecifico', 'IdObjInstitucional', 'IdUnidadEjecutora', 'IdGestion'], 'string', 'max' => 36],
            [['Codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente dos dígitos.'],
            [['Objetivo'], 'string', 'min' => 2, 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['IdObjEspecifico'], 'unique'],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdObjInstitucional'], 'exist', 'targetClass' => ObjetivoInstitucional::class, 'targetAttribute' => ['IdObjInstitucional' => 'IdObjInstitucional']],
            [['IdUnidadEjecutora'], 'exist', 'targetClass' => UnidadEjecutora::class, 'targetAttribute' => ['IdUnidadEjecutora' => 'IdUnidadEjecutora']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
        ];
    }

    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== Estado::ESTADO_VIGENTE) {
            return;
        }

        $id = $this->IdObjEspecifico
            ?: '00000000-0000-0000-0000-000000000000';

        $exists = self::find()
            ->where([
                'IdObjInstitucional' => $this->IdObjInstitucional,
                'IdUnidadEjecutora' => $this->IdUnidadEjecutora,
                'IdGestion' => $this->IdGestion,
                'Codigo' => $this->Codigo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andWhere(['<>', 'IdObjEspecifico', $id])
            ->exists();

        if ($exists) {
            $this->addError(
                $attribute,
                'El código del objetivo específico ya existe en el contexto actual.'
            );
        }
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdObjEspecifico' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        $contexto = Yii::$app->userContext->contexto();
        $idUnidadEjecutora = (string)($contexto?->IdUnidadEjecutora ?? '');
        $idGestion = (string)($contexto?->IdGestion ?? '');

        return self::find()->alias('Oe')
            ->select([
                'Oe.IdObjEspecifico',
                'Oe.IdObjInstitucional',
                'Oe.IdUnidadEjecutora',
                'Oes.IdObjEstrategico',
                "CONCAT(a.Codigo, p.Codigo, Oes.Codigo, '-', Oi.Codigo, '-', Oe.Codigo) AS Compuesto",
                'Oe.Codigo',
                'Oe.Objetivo',
                'Oe.IdGestion',
                'Oe.CodigoEstado',
                'Oe.CodigoUsuario',
            ])
            ->joinWith('objetivosInstitucionales Oi', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos Oes', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos.areaEstrategica a', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos.politicaEstrategica p', true, 'INNER JOIN')
            ->where([
                'Oe.IdUnidadEjecutora' => $idUnidadEjecutora,
                'Oe.IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'Oe.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'Oi.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'Oes.CodigoEstado', Estado::ESTADO_ELIMINADO]);
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

    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    public function getObjetivosInstitucionales(): ActiveQuery
    {
        return $this->hasOne(
            ObjetivoInstitucional::class,
            ['IdObjInstitucional' => 'IdObjInstitucional']
        );
    }

    public function getUnidadEjecutora(): ActiveQuery
    {
        return $this->hasOne(
            UnidadEjecutora::class,
            ['IdUnidadEjecutora' => 'IdUnidadEjecutora']
        );
    }

    public function getPeiGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }
}
