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
 * @property int $AreaEstrategica
 * @property int $PoliticaEstrategica
 * @property string $CodigoObjetivo
 * @property string $Objetivo
 * @property string $Producto
 * @property string $Indicador_Descripcion
 * @property string $Indicador_Formula
 * @property int $Pei
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property AreaEstrategica $areaEstrategica
 * @property PoliticaEstrategica $politicaEstrategica
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property PeI $pei
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
            [['CodigoObjEstrategico', 'AreaEstrategica', 'PoliticaEstrategica', 'CodigoObjetivo', 'Objetivo', 'Producto', 'Indicador_Descripcion', 'Indicador_Formula', 'Pei', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoObjEstrategico', 'AreaEstrategica', 'PoliticaEstrategica', 'Pei'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoObjetivo', 'CodigoEstado'], 'string', 'max' => 1],
            [['Objetivo', 'Producto', 'Indicador_Descripcion', 'Indicador_Formula'], 'string', 'max' => 450],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['AreaEstrategica', 'CodigoObjetivo', 'PoliticaEstrategica'], 'unique', 'targetAttribute' => ['AreaEstrategica', 'CodigoObjetivo', 'PoliticaEstrategica']],
            [['CodigoObjEstrategico'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['AreaEstrategica'], 'exist', 'skipOnError' => true, 'targetClass' => AreaEstrategica::class, 'targetAttribute' => ['AreaEstrategica' => 'CodigoAreaEstrategica']],
            [['PoliticaEstrategica'], 'exist', 'skipOnError' => true, 'targetClass' => PoliticaEstrategica::class, 'targetAttribute' => ['PoliticaEstrategica' => 'CodigoPoliticaEstrategica']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['Pei'], 'exist', 'skipOnError' => true, 'targetClass' => PeI::class, 'targetAttribute' => ['Pei' => 'CodigoPei']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'CodigoObjEstrategico' => 'Codigo Obj Estrategico',
            'AreaEstrategica' => 'Area Estrategica',
            'PoliticaEstrategica' => 'Politica Estrategica',
            'CodigoObjetivo' => 'Codigo Objetivo',
            'Objetivo' => 'Objetivo',
            'Producto' => 'Producto',
            'Indicador_Descripcion' => 'Indicador Descripcion',
            'Indicador_Formula' => 'Indicador Formula',
            'Pei' => 'Pei',
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
                'CONCAT(A.Codigo,Pe.Codigo,O.CodigoObjetivo) AS Compuesto',
                'O.CodigoObjetivo',
                'O.Objetivo',
                'O.Producto',
                'O.Indicador_Descripcion',
                'O.Indicador_Formula',
                'O.CodigoEstado',
                'O.CodigoUsuario',
                'O.Pei',
                'O.AreaEstrategica',
                'O.PoliticaEstrategica',
            ])
            ->joinWith('pei P', true, 'INNER JOIN')
            ->joinWith('areaEstrategica A', true, 'INNER JOIN')
            ->joinWith('politicaEstrategica Pe', true, 'INNER JOIN')
            ->where(['!=', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['o.Pei' => Yii::$app->contexto->getPei()])
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
     * Gets a query for [[ObjetivosInstitucionales]].
     *
     * @return ActiveQuery
     */
    public function getObjetivosInstitucionales(): ActiveQuery
    {
        return $this->hasMany(ObjetivoInstitucional::class, ['CodigoObjEstrategico' => 'CodigoObjEstrategico']);
    }

    /**
     * Gets a query for [[AreaEstrategica]].
     *
     * @return ActiveQuery
     */
    public function getAreaEstrategica(): ActiveQuery
    {
        return $this->hasOne(AreaEstrategica::class, ['CodigoAreaEstrategica' => 'AreaEstrategica']);
    }

    /**
     * Gets a query for [[PoliticaEstrategica]].
     *
     * @return ActiveQuery
     */
    public function getPoliticaEstrategica(): ActiveQuery
    {
        return $this->hasOne(PoliticaEstrategica::class, ['CodigoPoliticaEstrategica' => 'PoliticaEstrategica']);
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
     * Gets query for [[CodigoPei]].
     *
     * @return ActiveQuery
     */
    public function getPei(): ActiveQuery
    {
        return $this->hasOne(Pei::class, ['CodigoPei' => 'Pei']);
    }


}
