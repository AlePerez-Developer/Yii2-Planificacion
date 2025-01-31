<?php

namespace app\modules\PlanificacionCH\models;

use Yii;

/**
 * This is the model class for table "ConfiguracionesPlanificacionCH".
 *
 * @property int $id
 * @property string|null $GestionAcademica
 * @property int|null $ValorTeoricas
 * @property int|null $ValorPracticas
 * @property int|null $ValorLaboratorio
 * @property string|null $FechaInicio
 * @property string|null $FechaFin
 */
class ConfiguracionPlanificacionCH extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ConfiguracionesPlanificacionCH';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbAcademica');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ValorTeoricas', 'ValorPracticas', 'ValorLaboratorio'], 'integer'],
            [['FechaInicio', 'FechaFin'], 'safe'],
            [['GestionAcademica'], 'string', 'max' => 6],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'GestionAcademica' => 'Gestion Academica',
            'ValorTeoricas' => 'Valor Teoricas',
            'ValorPracticas' => 'Valor Practicas',
            'ValorLaboratorio' => 'Valor Laboratorio',
            'FechaInicio' => 'Fecha Inicio',
            'FechaFin' => 'Fecha Fin',
        ];
    }
}
