<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ObjetivosEspecificos".
 *
 * @property string $IdObjEspecifico
 * @property string $IdObjInstitucional
 * @property string $IdLlavePresupuestaria
 * @property string $Codigo
 * @property string $Objetivo
 * @property string $Producto
 * @property string $Indicador_Descripcion
 * @property string $Indicador_Formula
 * @property int $gestion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property LlavePresupuestaria $idLlavePresupuestaria
 * @property ObjetivoInstitucional $idObjInstitucional
 */
class ObjetivoEspecifico extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ObjetivosEspecificos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdObjEspecifico', 'IdObjInstitucional', 'IdLlavePresupuestaria'], 'string'],
            [['IdObjInstitucional', 'IdLlavePresupuestaria', 'Codigo', 'Objetivo', 'Producto', 'Indicador_Descripcion', 'Indicador_Formula', 'gestion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['gestion'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo', 'CodigoUsuario'], 'string', 'max' => 3],
            [['Objetivo', 'Producto'], 'string', 'max' => 200],
            [['Indicador_Descripcion', 'Indicador_Formula'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdObjEspecifico'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdObjInstitucional'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoInstitucional::class, 'targetAttribute' => ['IdObjInstitucional' => 'IdObjInstitucional']],
            [['IdLlavePresupuestaria'], 'exist', 'skipOnError' => true, 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdObjEspecifico' => 'Id Obj Especifico',
            'IdObjInstitucional' => 'Id Obj Institucional',
            'IdLlavePresupuestaria' => 'Id Llave Presupuestaria',
            'Codigo' => 'Codigo',
            'Objetivo' => 'Objetivo',
            'Producto' => 'Producto',
            'Indicador_Descripcion' => 'Indicador Descripcion',
            'Indicador_Formula' => 'Indicador Formula',
            'gestion' => 'Gestion',
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
     * Gets a query for [[IdLlavePresupuestaria]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     *
     */
    public function getIdLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }

    /**
     * Gets a query for [[IdObjInstitucional]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     *
     */
    public function getIdObjInstitucional(): ActiveQuery
    {
        return $this->hasOne(ObjetivoInstitucional::class, ['IdObjInstitucional' => 'IdObjInstitucional']);
    }
}
