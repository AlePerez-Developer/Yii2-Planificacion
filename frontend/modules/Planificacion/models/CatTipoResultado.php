<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "CatTiposResultados".
 *
 * @property string $IdTipoResultado
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property IndicadorEstrategico[] $indicadoresEstrategicos
 */

class CatTipoResultado extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'CatTiposResultados';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdTipoResultado'], 'string'],
            [['Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdTipoResultado'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?CatTipoResultado
    {
        return self::findOne(['IdTipoResultado' => $id, ['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll($search = '%%'): ActiveQuery
    {
        return self::find()->alias('U')
            ->select([
                'U.IdTipoResultado',
                'U.Descripcion',
                'U.Descripcion',
                'U.CodigoUsuario',
                'U.CodigoEstado',
            ])
            ->where(['!=', 'U.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'U.Descripcion', $search, false])
            ->orderBy(['U.IdTipoResultado' => SORT_ASC]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdTipoResultado' => 'Id Tipo Resultado',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
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
        return $this->hasMany(IndicadorEstrategico::class, ['IdTipoResultado' => 'IdTipoResultado']);
    }
}