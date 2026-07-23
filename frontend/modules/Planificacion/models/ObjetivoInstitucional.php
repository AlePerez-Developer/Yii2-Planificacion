<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * @property string $IdObjInstitucional
 * @property string $IdObjEstrategico
 * @property string $Codigo
 * @property string $Objetivo
 * @property string $Producto
 * @property string $IdGestion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ObjetivoEstrategico $idObjEstrategico
 * @property PeiGestion $idGestion
 * @property ObjetivoEspecifico[] $objetivosEspecificos
 */
class ObjetivoInstitucional extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ObjetivosInstitucionales';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdObjEstrategico', 'Codigo', 'Objetivo', 'Producto', 'IdGestion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdObjInstitucional', 'IdObjEstrategico', 'IdGestion'], 'string', 'max' => 36],
            [['Codigo'], 'string', 'length' => 2],
            [['Codigo'], 'match', 'pattern' => '/^[0-9]{2}$/', 'message' => 'El código debe estar compuesto por exactamente dos números (ej. 01, 15, 99).'],
            [['Objetivo', 'Producto'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['IdObjInstitucional'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdObjEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEstrategico::class, 'targetAttribute' => ['IdObjEstrategico' => 'IdObjEstrategico']],
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

        $id = $this->IdObjInstitucional == null ? '00000000-0000-0000-0000-000000000000' : $this->IdObjInstitucional;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'Gestion' => $this->IdGestion,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdObjInstitucional', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo de objetivo institucional ya existe');
        }
    }


    public static function listOne(string $id): ?ObjetivoInstitucional
    {
        return self::findOne(['IdObjInstitucional' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(): ActiveQuery
    {
        $contexto = Yii::$app->userContext->contexto();

        return self::find()->alias('Oi')
            ->select([
                'Oi.IdObjInstitucional',
                'Oi.IdObjEstrategico',
                "CONCAT(a.Codigo,p.Codigo,O.Codigo, '-', Oi.Codigo) AS Compuesto",
                'Oi.Codigo',
                'Oi.Objetivo',
                'Oi.Producto',
                'Oi.IdGestion',
                'Oi.CodigoEstado',
                'Oi.CodigoUsuario'
            ])
            ->joinWith('objetivosEstrategicos O', true, 'INNER JOIN')
            ->joinWith('objetivosEstrategicos.areaEstrategica a', true, 'INNER JOIN')
            ->joinWith('objetivosEstrategicos.politicaEstrategica p', true, 'INNER JOIN')
            ->joinWith('peiGestion g', true, 'INNER JOIN')
            ->where(['<>', 'OI.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'objetivosEstrategicos.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['Oi.IdGestion' => $contexto?->IdGestion])
            ->groupBy(['Oi.IdObjInstitucional', 'Oi.Codigo', 'Oi.Objetivo', 'Oi.Producto', 'Oi.IdGestion', 'Oi.CodigoEstado', 'Oi.CodigoUsuario',
                'Oi.IdObjEstrategico', 'O.Codigo', 'a.Codigo', 'p.Codigo',
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
     * realiza el soft delete de un registro.
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
     * Gets query for [[ObjetivosEstrategicos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getObjetivosEstrategicos(): ActiveQuery
    {
        return $this->hasOne(ObjetivoEstrategico::class, ['IdObjEstrategico' => 'IdObjEstrategico']);
    }

    /**
     * Gets query for [[ObjetivosEstrategicos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getPeiGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    /**
     * Gets query for [[ObjetivosEspecificos]].
     *
     * @return ActiveQuery
     */
    public function getObjetivosEspecificos(): ActiveQuery
    {
        return $this->hasMany(ObjetivoEspecifico::class, ['IdObjInstitucional' => 'IdObjInstitucional']);
    }
}
