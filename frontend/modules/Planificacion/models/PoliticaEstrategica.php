<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla "PoliticasEstrategicas".
 *
 * @property int $CodigoPoliticaEstrategica
 * @property int $CodigoAreaEstrategica
 * @property int $Codigo
 * @property string $Descripcion
 *
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 *
 * @property AreaEstrategica $area
 */
class PoliticaEstrategica extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'PoliticasEstrategicas';
    }

    public function rules(): array
    {
        return [
            [['CodigoAreaEstrategica', 'Codigo', 'Descripcion'], 'required'],
            [['CodigoPoliticaEstrategica', 'CodigoAreaEstrategica', 'Codigo'], 'integer'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoPoliticaEstrategica'], 'unique'],
            [['CodigoAreaEstrategica'], 'exist', 'skipOnError' => true, 'targetClass' => AreaEstrategica::class, 'targetAttribute' => ['CodigoAreaEstrategica' => 'CodigoAreaEstrategica']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'CodigoPoliticaEstrategica' => 'Código Política',
            'CodigoAreaEstrategica' => 'Área Estratégica',
            'Codigo' => 'Código',
            'Descripcion' => 'Descripción',
        ];
    }

    public static function listOne($codigo): ?PoliticaEstrategica
    {
        return self::findOne(['CodigoPoliticaEstrategica' => $codigo,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()->alias('P')
            ->select([
                'P.CodigoPoliticaEstrategica',
                'A.CodigoAreaEstrategica',
                'P.Codigo',
                'P.Descripcion',
                'P.CodigoUsuario',
                'P.CodigoEstado',
            ])
            ->joinWith('areaEstrategica A', true, 'INNER JOIN')
            ->where(['!=', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['A.CodigoPei' => Yii::$app->contexto->getPei()])
            ->orderBy(['P.Codigo' => SORT_ASC]);
    }

    public static function listAllByArea(int $area, string $search = '%%'): ActiveQuery
    {
        return self::find()->alias('P')
            ->select([
                'P.CodigoPoliticaEstrategica',
                'A.CodigoAreaEstrategica',
                'P.Codigo',
                'P.Descripcion',
                'P.CodigoUsuario',
                'P.CodigoEstado',
            ])
            ->joinWith('areaEstrategica A', true, 'INNER JOIN')
            ->Where(['A.CodigoAreaEstrategica' => $area])
            ->andwhere(['like', 'P.Descripcion', $search,false])
            ->orderBy(['P.Codigo' => SORT_ASC]);
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

    public function getAreaEstrategica(): ActiveQuery
    {
        return $this->hasOne(AreaEstrategica::class, ['CodigoAreaEstrategica' => 'CodigoAreaEstrategica']);
    }

    public function getObjetivoEstrategico(): ActiveQuery
    {
        return $this->hasOne(ObjetivoEstrategico::class, ['CodigoPoliticaEstrategica' => 'CodigoPoliticaEstrategica']);
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
