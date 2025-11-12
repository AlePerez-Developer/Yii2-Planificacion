<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "CatUnidadesIndicadores".
 *
 * @property string $IdUnidadIndicador
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property IndicadorEstrategico[] $indicadoresEstrategicos
 */
class CatUnidadIndicador extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'CatUnidadesIndicadores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUnidadIndicador'], 'string'],
            [['Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdUnidadIndicador'], 'unique'],
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
            'IdUnidadIndicador' => 'Id Unidad Indicador',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listOne(string $id): ?CatUnidadIndicador
    {
        return self::findOne(['IdUnidadIndicador' => $id, ['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll($search = '%%'): ActiveQuery
    {
        return self::find()->alias('U')
            ->select([
                'U.IdUnidadIndicador',
                'U.Descripcion',
                'U.Descripcion',
                'U.CodigoUsuario',
                'U.CodigoEstado',
            ])
            ->where(['!=', 'U.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'U.Descripcion', $search, false])
            ->orderBy(['U.IdUnidadIndicador' => SORT_ASC]);
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
     * Gets a query for [[IndicadoresEstrategicos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIndicadoresEstrategicos(): ActiveQuery
    {
        return $this->hasMany(IndicadorEstrategico::class, ['IdUnidadIndicador' => 'IdUnidadIndicador']);
    }
}
