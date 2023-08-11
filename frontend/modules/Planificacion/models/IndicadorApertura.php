<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "IndicadoresAperturas".
 *
 * @property int $CodigoIndicadorApertura
 * @property int $Indicador
 * @property int $Apertura
 * @property int $MetaObligatoria
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Indicador $indicador
 * @property Unidad $apertura
 * @property Usuario $codigoUsuario
 * @property Estado $codigoEstado
 */
class IndicadorApertura extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'IndicadoresAperturas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoIndicadorApertura', 'Indicador', 'Apertura', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoIndicadorApertura', 'Indicador', 'Apertura', 'MetaObligatoria'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoIndicadorApertura'], 'unique'],
            [['Indicador'], 'exist', 'skipOnError' => true, 'targetClass' => Indicador::class, 'targetAttribute' => ['Indicador' => 'CodigoIndicador']],
            [['Apertura'], 'exist', 'skipOnError' => true, 'targetClass' => Unidad::class, 'targetAttribute' => ['Apertura' => 'CodigoUnidad']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoIndicadorApertura' => 'Codigo Indicador Apertura',
            'Indicador' => 'Indicador',
            'Apertura' => 'Apertura',
            'MetaObligatoria' => 'Meta Obligatoria',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Gets query for [[Indicador]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicador()
    {
        return $this->hasOne(Indicador::class, ['CodigoIndicador' => 'Indicador']);
    }

    /**
     * Gets query for [[Apertura]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApertura()
    {
        return $this->hasOne(Unidad::class, ['CodigoUnidad' => 'Apertura']);
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoUsuario()
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[CodigoEstado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoEstado()
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }
}
