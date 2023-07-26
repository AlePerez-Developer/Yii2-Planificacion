<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "CategoriasIndicadores".
 *
 * @property int $CodigoCategoria
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property Indicador[] $indicadores
 */
class CategoriaIndicador extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CategoriasIndicadores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoCategoria', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoCategoria'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 200],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoCategoria'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoCategoria' => 'Codigo Categoria',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Gets query for [[CodigoEstado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoEstado()
    {
        return $this->hasOne(Estado::className(), ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoUsuario()
    {
        return $this->hasOne(Usuario::className(), ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[Indicadores]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicadores()
    {
        return $this->hasMany(Indicador::className(), ['Categoria' => 'CodigoCategoria']);
    }
}
