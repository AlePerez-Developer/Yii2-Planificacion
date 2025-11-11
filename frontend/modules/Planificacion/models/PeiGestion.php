<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "PeiGestion".
 *
 * @property string $IdGestion
 * @property string $IdPei
 * @property int $Gestion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property Pei $idPei
 */
class PeiGestion extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'PeiGestion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdGestion', 'IdPei'], 'string'],
            [['IdPei', 'Gestion', 'CodigoUsuario'], 'required'],
            [['Gestion'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdGestion'], 'unique'],
            [['IdPei', 'Gestion'], 'unique', 'targetAttribute' => ['IdPei', 'Gestion'], 'message' => 'ya existe una programacion de gestion con esos datos'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdPei'], 'exist', 'skipOnError' => true, 'targetClass' => PeI::class, 'targetAttribute' => ['IdPei' => 'IdPei']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdGestion' => 'Id Gestion',
            'IdPei' => 'Id Pei',
            'Gestion' => 'Gestion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Gets query for [[IdPei]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdPei(): ActiveQuery
    {
        return $this->hasOne(PeI::class, ['IdPei' => 'IdPei']);
    }

    /**
     * Gets a query for [[GestionProgramacion]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getGestionProgramacion(): ActiveQuery
    {
        return $this->hasMany(IndicadorEstrategicoProgramacionGestion::class, ['IdGestion' => 'IdGestion']);
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