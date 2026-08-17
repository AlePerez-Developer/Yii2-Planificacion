<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdIndicador
 * @property string $IdGestion
 * @property int $Codigo
 * @property string $CodigoEstado
 *
 * @property Indicador $indicador
 * @property PeiGestion $gestion
 */
class IndicadorPoa extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'IndicadoresPoa';
    }

    public function rules(): array
    {
        return [
            [['IdIndicador', 'IdGestion', 'Codigo', 'CodigoEstado'], 'required'],
            [['IdIndicador', 'IdGestion'], 'string', 'max' => 36],
            [['Codigo'], 'integer', 'min' => 1],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdIndicador'], 'unique'],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['IdIndicador'], 'exist', 'targetClass' => Indicador::class, 'targetAttribute' => ['IdIndicador' => 'IdIndicador']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
        ];
    }

    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== Estado::ESTADO_VIGENTE) {
            return;
        }

        $id = $this->IdIndicador ?: '00000000-0000-0000-0000-000000000000';
        $exists = self::find()
            ->where([
                'IdGestion' => $this->IdGestion,
                'Codigo' => $this->Codigo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andWhere(['<>', 'IdIndicador', $id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El código del indicador POA ya existe en la gestión activa.');
        }
    }

    public static function listAll(string $idGestion): ActiveQuery
    {
        return self::find()->alias('P')
            ->select([
                'P.IdIndicador',
                'P.IdGestion',
                'P.Codigo',
                'P.CodigoEstado',
                'I.Meta',
                'I.Descripcion',
                'I.LineaBase',
                'I.IdTipoResultado',
                'I.IdCategoriaIndicador',
                'I.IdUnidadIndicador',
                'I.CodigoUsuario',
            ])
            ->joinWith('indicador I', true, 'INNER JOIN')
            ->joinWith('indicador.catTiposResultados T', true, 'INNER JOIN')
            ->joinWith('indicador.catCategoriasIndicadores C', true, 'INNER JOIN')
            ->joinWith('indicador.catUnidadesIndicadores U', true, 'INNER JOIN')
            ->where(['P.IdGestion' => $idGestion])
            ->andWhere(['<>', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'T.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'C.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'U.CodigoEstado', Estado::ESTADO_ELIMINADO]);
    }

    public static function listOne(string $id, string $idGestion): ?self
    {
        return self::find()
            ->where(['IdIndicador' => $id, 'IdGestion' => $idGestion])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->with('indicador')
            ->one();
    }

    public static function listOneArray(string $id, string $idGestion): ?array
    {
        $modelo = self::listOne($id, $idGestion);
        if ($modelo === null || $modelo->indicador === null) {
            return null;
        }

        return [
            'IdIndicador' => $modelo->IdIndicador,
            'Codigo' => $modelo->Codigo,
            'Meta' => $modelo->indicador->Meta,
            'Descripcion' => $modelo->indicador->Descripcion,
            'LineaBase' => $modelo->indicador->LineaBase,
            'IdTipoResultado' => $modelo->indicador->IdTipoResultado,
            'IdCategoriaIndicador' => $modelo->indicador->IdCategoriaIndicador,
            'IdUnidadIndicador' => $modelo->indicador->IdUnidadIndicador,
        ];
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

    public function getIndicador(): ActiveQuery
    {
        return $this->hasOne(Indicador::class, ['IdIndicador' => 'IdIndicador']);
    }

    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getProgramaciones(): ActiveQuery
    {
        return $this->hasMany(
            ProgramacionIndicadorPoaGestion::class,
            ['IdIndicadorPoa' => 'IdIndicador']
        );
    }
}
