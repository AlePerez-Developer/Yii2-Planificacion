<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use Yii;

/**
 * Modelo para la tabla "PoliticasEstrategicas".
 *
 * @property string $IdPoliticaEstrategica
 * @property string $IdAreaEstrategica
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
 * @property AreaEstrategica $idAreaEstrategica
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
            [['IdPoliticaEstrategica', 'IdAreaEstrategica'], 'string'],
            [['IdAreaEstrategica', 'Codigo', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Codigo'], 'integer','min' => 1, 'max' => 9,],
            [['FechaHoraRegistro'], 'safe'],
            [['IdAreaEstrategica','IdPoliticaEstrategica'], 'string', 'max' => 36],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdPoliticaEstrategica'], 'unique'],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdAreaEstrategica'], 'exist', 'skipOnError' => true, 'targetClass' => AreaEstrategica::class, 'targetAttribute' => ['IdAreaEstrategica' => 'IdAreaEstrategica']],
        ];
    }

    /**
     * Valida que no exista otra política activa con el mismo código y área estratégica.
     *
     * @param string $attribute
     * @used-by rules()
     * @noinspection PhpUnused
     */
    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== 'V') {
            return;
        }

        $id = $this->IdPoliticaEstrategica == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdPoliticaEstrategica;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'IdAreaEstrategica' => $this->IdAreaEstrategica,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdPoliticaEstrategica', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de Politica Estraetgica ya existe con el  Área Estratégica seleccionada.');
        }
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdPoliticaEstrategica' => 'Id Politica Estrategica',
            'IdAreaEstrategica' => 'Id Area Estrategica',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listOne(string $id): ?PoliticaEstrategica
    {
        return self::findOne(['IdPoliticaEstrategica' => $id, ['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()->alias('P')
            ->select([
                'P.IdPoliticaEstrategica',
                'A.IdAreaEstrategica',
                'P.Codigo',
                'P.Descripcion',
                'P.CodigoUsuario',
                'P.CodigoEstado',
            ])
            ->joinWith('areaEstrategica A', true, 'INNER JOIN')
            ->where(['!=', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['A.IdPei' => Yii::$app->contexto->getPei()])
            ->orderBy(['P.Codigo' => SORT_ASC]);
    }

    public static function listAllByArea(string $idAreaEstrategica, string $search = '%%'): ActiveQuery
    {
        return self::find()->alias('P')
            ->select([
                'P.IdPoliticaEstrategica',
                'A.IdAreaEstrategica',
                'P.Codigo',
                'P.Descripcion',
                'P.CodigoUsuario',
                'P.CodigoEstado',
            ])
            ->joinWith('areaEstrategica A', true, 'INNER JOIN')
            ->Where(['A.IdAreaEstrategica' => $idAreaEstrategica])
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

    /**
     * Gets a query for [[IdAreaEstrategica]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getAreaEstrategica(): ActiveQuery
    {
        return $this->hasOne(AreaEstrategica::class, ['IdAreaEstrategica' => 'IdAreaEstrategica']);
    }

    /**
     * Gets a query for [[ObjetivoEstrategico]].
     *
     * @return ActiveQuery
     */
    public function getObjetivosEstrategicos(): ActiveQuery
    {
        return $this->hasMany(ObjetivoEstrategico::class, ['IdPoliticaEstrategica' => 'IdPoliticaEstrategica']);
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
