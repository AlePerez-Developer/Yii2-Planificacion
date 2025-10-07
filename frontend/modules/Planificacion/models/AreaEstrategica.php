<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use Yii;

/**
 * Modelo para la tabla "AreasEstrategicas".
 *
 * @property int $CodigoAreaEstrategica
 * @property int $CodigoPei
 * @property int $Codigo
 * @property string $Descripcion
 *
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property Pei $pei
 *
 * @property PoliticaEstrategica[] $politicasEstrategicas
 */
class AreaEstrategica extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'AreasEstrategicas';
    }

    public function rules(): array
    {
        return [
            [['CodigoPei', 'Codigo', 'Descripcion'], 'required'],
            [['CodigoAreaEstrategica', 'CodigoPei', 'Codigo'], 'integer'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoAreaEstrategica'], 'unique'],
            //[['Codigo'], 'unique'],
            [['CodigoPei'], 'exist', 'skipOnError' => true, 'targetClass' => Pei::class, 'targetAttribute' => ['CodigoPei' => 'CodigoPei']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'CodigoAreaEstrategica' => 'Codigo Área Estratégica',
            'CodigoPei' => 'PEI',
            'Codigo' => 'Código',
            'Descripcion' => 'Descripción',
        ];
    }

    public static function listOne($codigo): ?AreaEstrategica
    {
        return self::findOne(['CodigoAreaEstrategica' => $codigo,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll($search = '%%'): ActiveQuery
    {
        return self::find()->alias('A')
            ->select([
                'A.CodigoAreaEstrategica',
                'P.CodigoPei',
                'A.Codigo',
                'A.Descripcion',
                'P.DescripcionPEI',
                'P.GestionInicio',
                'P.GestionFin',
                'P.FechaAprobacion',
                'A.CodigoUsuario',
                'A.CodigoEstado',
            ])
            ->joinWith('pei P', true, 'INNER JOIN')
            ->where(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'A.Descripcion', $search,false])
            ->andWhere(['!=', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['A.CodigoPei' => Yii::$app->contexto->getPei()])
            ->orderBy(['A.Codigo' => SORT_ASC]);
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
    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }

    /**
     * Gets query for [[PoliticaEstrategica]].
     *
     * @return ActiveQuery
     */
    public function getPoliticasEstrategicas(): ActiveQuery
    {
        return $this->hasMany(PoliticaEstrategica::class, ['CodigoAreaEstrategica' => 'CodigoAreaEstrategica']);
    }

    public function getPei(): ActiveQuery
    {
        return $this->hasOne(Pei::class, ['CodigoPei' => 'CodigoPei']);
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
}
