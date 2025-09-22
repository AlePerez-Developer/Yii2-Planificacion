<?php

namespace app\modules\Planificacion\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "ObjetivosEstrategicos".
 *
 * @property int $CodigoObjEstrategico
 * @property string $CodigoObjetivo
 * @property string $Objetivo
 * @property int $CodigoPei
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property PEI $codigoPei
 * @property ObjetivoInstitucional[] $objetivosInstitucionales
 */

class ObjetivoEstrategico extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ObjetivosEstrategicos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['CodigoObjEstrategico', 'CodigoObjetivo', 'Objetivo', 'CodigoPei', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoObjEstrategico', 'CodigoPei'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoObjetivo', 'CodigoUsuario'], 'string', 'max' => 3],
            [['Objetivo'], 'string', 'max' => 450],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoObjEstrategico'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoPei'], 'exist', 'skipOnError' => true, 'targetClass' => Pei::class, 'targetAttribute' => ['CodigoPei' => 'CodigoPei']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'CodigoObjEstrategico' => 'Codigo Obj Estrategico',
            'CodigoObjetivo' => 'Codigo Objetivo',
            'Objetivo' => 'Objetivo',
            'CodigoPei' => 'Codigo pei',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listOne($codigo): ?ObjetivoEstrategico
    {
        return self::findOne(['CodigoObjEstrategico' => $codigo,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()->alias('O')
            ->select([
                'O.CodigoObjEstrategico',
                'P.DescripcionPEI',
                'P.GestionInicio',
                'P.GestionFin',
                'O.CodigoObjetivo',
                'O.Objetivo',
                'O.CodigoEstado',
                'O.CodigoUsuario',
                'P.FechaAprobacion'
            ])
            ->joinWith('pei P', true, 'INNER JOIN')
            ->where(['!=', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['o.CodigoPei' => Yii::$app->contexto->getPei()])
            ->orderBy(['CodigoObjetivo' => SORT_ASC]);
    }

    /**
     * alterna el estado del modelo V/C.
     *
     * @return void
     */
    public function cambiarEstado(): void
    {
        $this->CodigoEstado = $this->CodigoEstado == Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
    }

    /**
     * realiza el soft delete de un registro.
     *
     * @return void
     */
    public function eliminarObjetivo(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }

    /**
     * Gets query for [[ObjetivosInstitucionales]].
     *
     * @return ActiveQuery
     */
    public function getObjetivosInstitucionales(): ActiveQuery
    {
        return $this->hasMany(ObjetivoInstitucional::class, ['CodigoObjEstrategico' => 'CodigoObjEstrategico']);
    }

    /**
     * Gets query for [[CodigoEstado]].
     *
     * @return ActiveQuery
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return ActiveQuery
     */
    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[CodigoPei]].
     *
     * @return ActiveQuery
     */
    public function getPei(): ActiveQuery
    {
        return $this->hasOne(Pei::class, ['CodigoPei' => 'CodigoPei']);
    }

    public function getCodigoPei(): ActiveQuery
    {
        return $this->hasOne(Pei::class, ['CodigoPei' => 'CodigoPei']);
    }
}
