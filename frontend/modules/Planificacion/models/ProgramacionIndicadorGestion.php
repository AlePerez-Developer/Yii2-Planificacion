<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "ProgramacioesnInidicadoresGestiones".
 *
 * @property string $IdProgramacionIndicadorGestio
 * @property string $IdIndicadorEstrategico
 * @property string $IdLlavePresupuestaria
 * @property string $IdGestion
 * @property int $MetaProgramada
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Usuario $codigoUsuario
 * @property PeiGestion $idGestion
 * @property IndicadorEstrategico $indicadorEstrategico
 * @property LlavePresupuestaria $llavePresupuestaria
 */
class ProgramacionIndicadorGestion extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ProgramacionesIndicadoresGestiones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdProgramacionIndicadorGestio', 'IdIndicadorEstrategico', 'IdLlavePresupuestaria', 'IdGestion'], 'string'],
            [['IdIndicadorEstrategico', 'IdLlavePresupuestaria', 'IdGestion', 'CodigoUsuario'], 'required'],
            [['MetaProgramada'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdProgramacionIndicadorGestio'], 'unique'],
            [['IdLlavePresupuestaria'], 'exist', 'skipOnError' => true, 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdIndicadorEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => IndicadorEstrategico::class, 'targetAttribute' => ['IdIndicadorEstrategico' => 'IdIndicadorEstrategico']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdProgramacionIndicadorGestio' => 'Id Programacion Indicador Gestio',
            'IdIndicadorEstrategico' => 'Id Indicador Estrategico',
            'IdLlavePresupuestaria' => 'Id Llave Presupuestaria',
            'IdGestion' => 'Id Gestion',
            'MetaProgramada' => 'Meta Programada',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listAllbyGestion(string $idIndicadorEstrategico, string $idGestion): ActiveQuery
    {
        return self::find()->alias('P')
            ->select([
                'P.IdProgramacionIndicadorGestio',
                'L.Llave',
                'L.Descripcion',
                'P.MetaProgramada as Meta',
                'G.IdGestion',
                'I.IDIndicadorEstrategico',
                'L.IdLlavePresupuestaria',
            ])
            ->joinWith('gestion G', true, 'INNER JOIN')
            ->joinWith('indicadorEstrategico I', true, 'INNER JOIN')
            ->joinWith('llavePresupuestaria L', true, 'INNER JOIN')
            ->joinWith('llavePresupuestaria.da Ld', true, 'INNER JOIN')
            ->joinWith('llavePresupuestaria.ue Lu', true, 'INNER JOIN')
            ->joinWith('llavePresupuestaria.proyecto.programa Lpr', true, 'INNER JOIN')
            ->joinWith('llavePresupuestaria.proyecto Lpy', true, 'INNER JOIN')
            ->joinWith('llavePresupuestaria.actividad La', true, 'INNER JOIN')
            ->where(['!=', 'G.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['P.IdGestion' => $idGestion])
            ->andWhere(['P.IdIndicadorEstrategico' => $idIndicadorEstrategico]);
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
    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    /**
     * Gets a query for [[IdIndicadorEstrategico]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIndicadorEstrategico(): ActiveQuery
    {
        return $this->hasOne(IndicadorEstrategico::class, ['IdIndicadorEstrategico' => 'IdIndicadorEstrategico']);
    }

    /**
     * Gets a query for [[IdLlavePresupuestaria]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }
}
