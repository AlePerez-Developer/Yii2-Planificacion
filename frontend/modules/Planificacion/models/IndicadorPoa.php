<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "IndicadoresEstrategicos".
 *
 * @property string $IdIndicadorPoa
 * @property string $IdObjEspecifico
 * @property int $Codigo
 * @property int $Meta
 * @property string $Descripcion
 * @property int $LineaBase
 * @property string $IdTipoResultado
 * @property string $IdCategoriaIndicador
 * @property string $IdUnidadIndicador
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property CatCategoriaIndicador $catCategoriasIndicadores
 * @property CatTipoResultado $catTiposResultados
 * @property CatUnidadIndicador $catUnidadesIndicadores
 * @property Estado $estados
 * @property Usuario $usuarios
 */

class IndicadorPoa extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'IndicadoresPoa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdIndicadorPoa', 'IdObjEspecifico', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador'], 'string', 'max' => 36],
            [['IdObjEspecifico', 'Codigo', 'Meta', 'Descripcion', 'LineaBase', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Meta', 'LineaBase'], 'integer', 'min' => 0],
            [['Codigo'], 'integer', 'min' => 1],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdIndicadorPoa'], 'unique'],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['IdObjEspecifico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEspecifico::class, 'targetAttribute' => ['IdObjEspecifico' => 'IdObjEspecifico']],
            [['IdTipoResultado'], 'exist', 'skipOnError' => true, 'targetClass' => CatTipoResultado::class, 'targetAttribute' => ['IdTipoResultado' => 'IdTipoResultado']],
            [['IdCategoriaIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => CatCategoriaIndicador::class, 'targetAttribute' => ['IdCategoriaIndicador' => 'IdCategoriaIndicador']],
            [['IdUnidadIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => CatUnidadIndicador::class, 'targetAttribute' => ['IdUnidadIndicador' => 'IdUnidadIndicador']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
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

        $id = $this->IdIndicadorPoa == null ? '00000000-0000-0000-0000-000000000000' : $this->IdIndicadorPoa;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdIndicadorPoa', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de indicador poa ya existe');
        }
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdIndicadorPoa' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(string $idLlavePresupuestaria, int $gestion): ActiveQuery
    {
        return self::find()->alias('I')
            ->select([
                'I.IdIndicadorPoa',
                'I.IdObjEspecifico',
                "CONCAT(a.Codigo,p.Codigo,Oes.Codigo,'-',Oi.Codigo,'-',Oe.Codigo) AS Compuesto",
                'I.Codigo',
                'I.Meta',
                'I.Descripcion',
                'I.LineaBase',
                'C.IdCategoriaIndicador',
                'T.IdTipoResultado',
                'U.IdUnidadIndicador',
                'I.CodigoEstado',
                'I.CodigoUsuario',
            ])
            ->joinWith('objetivosEspecificos Oe', true, 'INNER JOIN')
            ->joinWith('objetivosEspecificos.objetivosInstitucionales Oi', true, 'INNER JOIN')
            ->joinWith('objetivosEspecificos.objetivosInstitucionales.objetivosEstrategicos Oes', true, 'INNER JOIN')
            ->joinWith('objetivosEspecificos.objetivosInstitucionales.objetivosEstrategicos.areaEstrategica a', true, 'INNER JOIN')
            ->joinWith('objetivosEspecificos.objetivosInstitucionales.objetivosEstrategicos.politicaEstrategica p', true, 'INNER JOIN')
            ->joinWith('catCategoriasIndicadores C', true, 'INNER JOIN')
            ->joinWith('catTiposResultados T', true, 'INNER JOIN')
            ->joinWith('catUnidadesIndicadores U', true, 'INNER JOIN')
            ->where(['!=', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'Oe.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'C.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'T.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'U.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->groupBy(['I.IdIndicadorPoa', 'I.IdObjEspecifico', 'a.Codigo', 'p.Codigo', 'Oes.Codigo','Oi.Codigo','Oe.Codigo',
                'I.Codigo', 'I.Meta', 'I.Descripcion', 'I.LineaBase',
                'C.IdCategoriaIndicador', 'T.IdTipoResultado', 'U.IdUnidadIndicador',
                'I.CodigoEstado', 'I.CodigoUsuario']);
    }

    public function cambiarEstado(): void
    {
        $this->CodigoEstado = $this->CodigoEstado === Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
    }

    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }

    /**
     * Gets a query for [[CatCategoriasIndicadores]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getCatCategoriasIndicadores(): ActiveQuery
    {
        return $this->hasOne(CatCategoriaIndicador::class, ['IdCategoriaIndicador' => 'IdCategoriaIndicador']);
    }

    /**
     * Gets a query for [[CatTiposResultados]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getCatTiposResultados(): ActiveQuery
    {
        return $this->hasOne(CatTipoResultado::class, ['IdTipoResultado' => 'IdTipoResultado']);
    }

    /**
     * Gets a query for [[CatUnidadesIndicadores]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getCatUnidadesIndicadores(): ActiveQuery
    {
        return $this->hasOne(CatUnidadIndicador::class, ['IdUnidadIndicador' => 'IdUnidadIndicador']);
    }

    /**
     * Gets query for [[IdObjEspecifico]].
     *
     * @return ActiveQuery
     */
    public function getObjetivosEspecificos()
    {
        return $this->hasOne(ObjetivoEspecifico::class, ['IdObjEspecifico' => 'IdObjEspecifico']);
    }
}
