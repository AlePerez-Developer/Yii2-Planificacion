<?php

namespace app\modules\PlanificacionCH\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "ControlEnvioPlanificacionCH".
 *
 * @property string $GestionAcademica
 * @property string $CodigoCarrera
 * @property string $NumeroPlanEstudios
 * @property string $CodigoSede
 * @property string $CodigoEstado
 * @property string|null $FechaEnvio
 */
class ControlEnvioPlanificacionCH extends ActiveRecord
{
    public static function tableName()
    {
        return 'ControlEnvioPlanificacionCH';
    }

}