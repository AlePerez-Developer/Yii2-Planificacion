<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdObjEspecifico
 * @property string $IdObjInstitucional
 * @property string IdLlavePresupuestaria
 * @property string $Codigo
 * @property string $Objetivo
 * @property string $Producto
 * @property string $IdGestion
 * @property string $Indicador_Descripcion
 * @property string $Indicador_Formula
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ObjetivoEstrategico $idObjEstrategico
 * @property PeiGestion $idGestion
 * @property ObjetivoEspecifico[] $objetivosEspecificos
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
            [['IdObjInstitucional', 'IdLlavePresupuestaria', 'Codigo', 'Objetivo', 'Producto', 'IdGestion', 'Indicador_Descripcion', 'Indicador_Formula', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdObjEspecifico', 'IdObjInstitucional', 'IdLlavePresupuestaria', 'IdGestion'], 'string', 'max' => 36],
            [['Codigo'], 'match', 'pattern' => '/^\d{2}$/'],
            [['Objetivo', 'Producto', 'Indicador_Descripcion', 'Indicador_Formula',], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdObjInstitucional'], 'exist', 'targetClass' => ObjetivoInstitucional::class, 'targetAttribute' => ['IdObjInstitucional' => 'IdObjInstitucional']],
            [['IdLlavePresupuestaria'], 'exist', 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
        ];
    }

    /**
     * Válida que no exista otra política activa con el mismo código y área estratégica.
     *
     * @param string $attribute
     * @used-by      rules()
     * @noinspection PhpUnused
     */
    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== 'V') {
            return;
        }

        $id = $this->IdObjEspecifico == null ? '00000000-0000-0000-0000-000000000000' : $this->IdObjEspecifico;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'IdObjInstitucional' => $this->IdObjInstitucional,
                'IdGestion' => $this->IdGestion,
                'IdLlavePresupuestaria' => $this->IdLlavePresupuestaria,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdObjEspecifico', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de Objetivo especifico ya existe en el contexto actual');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdObjEspecifico' => 'Id Obj Especifico',
            'IdObjInstitucional' => 'Id Obj Institucional',
            'IdLlavePresupuestaria' => 'Llave Presupuestaria',
            'IdGestion' => 'Id Gestion',
            'Codigo' => 'Codigo',
            'Objetivo' => 'Objetivo',
            'Producto' => 'Producto',
            'Indicador_Descripcion' => 'Indicador Descripcion',
            'Indicador_Formula' => 'Indicador Formula',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listOne(string $id): ?ObjetivoEspecifico
    {
        return self::findOne(['IdObjEspecifico' => $id, ['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO]]);
    }


    public static function listAll(): ActiveQuery
    {
        $contexto = Yii::$app->userContext->contexto();

        return self::find()->alias('Oe')
            ->select([
                'Oe.IdObjEspecifico',
                'Oi.IdObjInstitucional',
                'Oes.IdObjEstrategico',
                'Oe.IdLlavePresupuestaria',
                "CONCAT(a.Codigo,p.Codigo,Oes.Codigo, '-', Oi.Codigo, '-', Oe.Codigo) AS Compuesto",
                'Oe.Codigo',
                'Oe.Objetivo',
                'Oe.Producto',
                'Oe.Indicador_Descripcion',
                'Oe.Indicador_Formula',
                'g.IdGestion',
                'Oe.CodigoEstado',
                'Oe.CodigoUsuario'
            ])
            ->joinWith('objetivosInstitucionales Oi', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos Oes', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos.areaEstrategica a', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos.politicaEstrategica p', true, 'INNER JOIN')
            ->joinWith('peiGestion g', true, 'INNER JOIN')
            ->where(['<>', 'Oe.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'Oi.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'Oes.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['Oe.IdGestion' => $contexto?->IdGestion])
            ->andWhere(['Oe.IdLlavePresupuestaria' => $contexto?->IdLlavePresupuestaria])
            ->groupBy(['Oe.idObjEspecifico',
                'Oi.IdObjInstitucional',
                'Oes.IdObjEstrategico',
                'Oe.IdLlavePresupuestaria',
                'a.Codigo' , 'p.Codigo', 'Oes.Codigo', 'Oi.Codigo', 'Oe.Codigo',
                'Oe.Codigo',
                'Oe.Objetivo',
                'Oe.Producto',
                'Oe.Indicador_Descripcion',
                'Oe.Indicador_Formula',
                'g.IdGestion',
                'Oe.CodigoEstado',
                'Oe.CodigoUsuario'
            ]);
    }

    /**
     * Alterna el estado del modelo V/C.
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
     * Realiza el soft delete de un registro.
     *
     * @return void
     */
    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
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
     * @noinspection PhpUnused
     */
    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[ObjetivosInstitucional]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getObjetivosInstitucionales(): ActiveQuery
    {
        return $this->hasOne(ObjetivoInstitucional::class, ['IdObjInstitucional' => 'IdObjInstitucional']);
    }

    /**
     * Gets query for [[PeiGestion]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdPresupuestaria' => 'IdPresupuestaria']);
    }

    /**
     * Gets query for [[PeiGestion]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getPeiGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getIndicadoresPoa(): ActiveQuery
    {
        return $this->hasMany(IndicadorPoa::class, ['IdObjEspecifico' => 'IdObjEspecifico']);
    }
}
