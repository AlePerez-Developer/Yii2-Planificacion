<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "FODAUnidad".
 *
 * @property string $IdFoda
 * @property string $IdDa
 * @property string $IdGestion
 * @property string|null $Descripcion
 * @property string|null $Tipo
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property Da $da
 * @property PeiGestion $gestion
 */
class FODAUnidad extends ActiveRecord
{
    public const TIPO_FORTALEZA = 'Fortaleza';
    public const TIPO_DEBILIDAD = 'Debilidad';
    public const TIPO_OPORTUNIDAD = 'Oportunidad';
    public const TIPO_AMENAZA = 'Amenaza';

    public static function tableName(): string
    {
        return 'FODAUnidad';
    }

    public static function tipos(): array
    {
        return [
            self::TIPO_FORTALEZA => self::TIPO_FORTALEZA,
            self::TIPO_DEBILIDAD => self::TIPO_DEBILIDAD,
            self::TIPO_OPORTUNIDAD => self::TIPO_OPORTUNIDAD,
            self::TIPO_AMENAZA => self::TIPO_AMENAZA,
        ];
    }

    public function rules(): array
    {
        return [
            [['Descripcion'], 'default', 'value' => null],
            [['IdDa', 'IdGestion', 'Descripcion', 'Tipo', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdFoda', 'IdDa', 'IdGestion'], 'string', 'max' => 36],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 500],
            [['Tipo'], 'in', 'range' => array_values(self::tipos())],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdFoda'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdDa'], 'exist', 'skipOnError' => true, 'targetClass' => Da::class, 'targetAttribute' => ['IdDa' => 'IdDa']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'IdFoda' => 'Id Foda',
            'IdDa' => 'Id Da',
            'IdGestion' => 'Id Gestion',
            'Descripcion' => 'Descripcion',
            'Tipo' => 'Tipo',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listAll(string $idDa, string $idGestion): ActiveQuery
    {
        return self::find()->alias('F')
            ->where([
                'F.IdDa' => $idDa,
                'F.IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'F.CodigoEstado', Estado::ESTADO_ELIMINADO]);
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

    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    public function getDa(): ActiveQuery
    {
        return $this->hasOne(Da::class, ['IdDa' => 'IdDa']);
    }

    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }
}
