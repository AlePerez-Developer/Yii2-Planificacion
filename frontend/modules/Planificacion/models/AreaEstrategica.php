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
 * @property string $IdAreaEstrategica
 * @property string $IdPei
 * @property int $Codigo
 * @property string $Descripcion
 *
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property Pei $idPei
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
            [['IdAreaEstrategica', 'IdPei'], 'string'],
            [['IdPei', 'Codigo', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Codigo'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['IdPei'], 'string', 'max' => 36],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdAreaEstrategica'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdPei'], 'exist', 'skipOnError' => true, 'targetClass' => PeI::class, 'targetAttribute' => ['IdPei' => 'IdPei']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'IdAreaEstrategica' => 'Id Area Estrategica',
            'IdPei' => 'Id Pei',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listOne(string $id): ?AreaEstrategica
    {
        return self::findOne(['IdAreaEstrategica' => $id, ['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll($search = '%%'): ActiveQuery
    {
        return self::find()->alias('A')
            ->select([
                'A.IdAreaEstrategica',
                'P.IdPei',
                'A.Codigo',
                'A.Descripcion',
                'A.CodigoUsuario',
                'A.CodigoEstado',
            ])
            ->joinWith('pei P', true, 'INNER JOIN')
            ->where(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'A.Descripcion', $search,false])
            ->andWhere(['!=', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['A.IdPei' => Yii::$app->contexto->getPei()])
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
        return $this->hasMany(PoliticaEstrategica::class, ['IdAreaEstrategica' => 'IdAreaEstrategica']);
    }

    /**
     * Gets query for [[IdPei]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     * /
     */
    public function getPei(): ActiveQuery
    {
        return $this->hasOne(Pei::class, ['IdPei' => 'IdPei']);
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
