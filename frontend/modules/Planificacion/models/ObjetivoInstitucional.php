<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "ObjetivosInstitucionales".
 *
 * @property string $IdObjInstitucional
 * @property string $IdObjEstrategico
 * @property string $Codigo
 * @property string $Objetivo
 * @property string $Producto
 * @property int $Gestion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property ObjetivoEstrategico $idObjEstrategico
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
            [['IdObjInstitucional', 'IdObjEstrategico'], 'string'],
            [['IdObjEstrategico', 'Codigo', 'Objetivo', 'Producto', 'gestion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['gestion'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo', 'CodigoUsuario'], 'string', 'max' => 3],
            [['Objetivo', 'Producto'], 'string', 'max' => 200],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdObjInstitucional'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdObjEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEstrategico::class, 'targetAttribute' => ['IdObjEstrategico' => 'IdObjEstrategico']],
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

        $id = $this->IdObjInstitucional == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdObjInstitucional;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'IdObjetivoEstrategico' => $this->IdObjInstitucional,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdObjInstitucional', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de Objetivo institucional ya existe');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdObjInstitucional' => 'Id Obj Institucional',
            'IdObjEstrategico' => 'Id Obj Estrategico',
            'Codigo' => 'Codigo',
            'Objetivo' => 'Objetivo',
            'Producto' => 'Producto',
            'gestion' => 'Gestion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listOne(string $id): ?ObjetivoInstitucional
    {
        return self::findOne(['IdObjInstitucional' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(string $search = '%%'): ActiveQuery
    {
        return self::find()->alias('O')
            ->select([
                'O.IdObjInstitucional',
                'CONCAT(Ae.Codigo,Pe.Codigo,O.Codigo) AS Compuesto',
                'O.Codigo',
                'O.Objetivo',
                'O.Producto',
                'O.Indicador_Descripcion',
                'O.Indicador_Formula',
                'O.CodigoEstado',
                'O.CodigoUsuario',
                'P.IdPei',
                'Ae.IdAreaEstrategica',
                'Pe.IdPoliticaEstrategica',
            ])
            ->joinWith('objetivoEstrategico Oe', true, 'INNER JOIN')
            ->where(['!=', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'Oe.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'O.Objetivo', $search,false]);
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
     * Gets a query for [[IdObjEstrategico]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     *
     */
    public function getIdObjEstrategico(): ActiveQuery
    {
        return $this->hasOne(ObjetivoEstrategico::class, ['IdObjEstrategico' => 'IdObjEstrategico']);
    }

    /**
     * Gets a query for [[ObjetivosEspecificos]].
     *
     * @return ActiveQuery
     */
    public function getObjetivosEspecificos(): ActiveQuery
    {
        return $this->hasMany(ObjetivoEspecifico::class, ['IdObjInstitucional' => 'IdObjInstitucional']);
    }
}