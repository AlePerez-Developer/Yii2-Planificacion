<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "IndicadorEstrategicoProgramacionGestion".
 *
 * @property string $IdProgramacionGestion
 * @property string $IdGestion
 * @property string $IdIndicadorEstrategico
 * @property int $MetaProgramada
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property PeiGestion $idGestion
 * @property IndicadorEstrategico $idIndicadorEstrategico
 */

class IndicadorEstrategicoProgramacionGestion extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'IndicadorEstrategicoProgramacionGestion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdProgramacionGestion', 'IdGestion', 'IdIndicadorEstrategico'], 'string'],
            [['IdGestion', 'IdIndicadorEstrategico', 'MetaProgramada', 'CodigoUsuario'], 'required'],
            [['MetaProgramada'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdProgramacionGestion'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdIndicadorEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => IndicadorEstrategico::class, 'targetAttribute' => ['IdIndicadorEstrategico' => 'IdIndicadorEstrategico']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdProgramacionGestion' => 'Id Programacion Gestion',
            'IdGestion' => 'Id Gestion',
            'IdIndicadorEstrategico' => 'Id Indicador Estrategico',
            'MetaProgramada' => 'Meta Programada',
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
     * Gets a query for [[IdGestion]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    /**
     * Gets a query for [[IdIndicadorEstrategico]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdIndicadorEstrategico(): ActiveQuery
    {
        return $this->hasOne(IndicadorEstrategico::class, ['IdIndicadorEstrategico' => 'IdIndicadorEstrategico']);
    }
}