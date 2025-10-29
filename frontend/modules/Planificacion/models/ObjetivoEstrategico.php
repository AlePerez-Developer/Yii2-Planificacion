<?php

namespace app\modules\Planificacion\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "ObjetivosEstrategicos".
 *
 * @property string $IdObjEstrategico
 * @property string $IdAreaEstrategica
 * @property string $IdPoliticaEstrategica
 * @property int $Codigo
 * @property string $Objetivo
 * @property string $Producto
 * @property string $Indicador_Descripcion
 * @property string $Indicador_Formula
 * @property string $IdPei
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property AreaEstrategica $idAreaEstrategica
 * @property PoliticaEstrategica $idPoliticaEstrategica
 * @property Pei $idPei
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property ObjetivoInstitucional[] $objetivosInstitucionales
 */

class ObjetivoEstrategico extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ObjetivosEstrategicos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdObjEstrategico', 'IdAreaEstrategica', 'IdPoliticaEstrategica', 'IdPei'], 'string', 'max' => 36],
            [['IdAreaEstrategica', 'IdPoliticaEstrategica', 'Codigo', 'Objetivo', 'Producto', 'Indicador_Descripcion', 'Indicador_Formula', 'IdPei', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'integer','min' => 1, 'max' => 9,],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['Objetivo', 'Producto', 'Indicador_Descripcion', 'Indicador_Formula'], 'string', 'max' => 500],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['IdObjEstrategico'], 'unique'],
            [['IdAreaEstrategica'], 'exist', 'skipOnError' => true, 'targetClass' => AreaEstrategica::class, 'targetAttribute' => ['IdAreaEstrategica' => 'IdAreaEstrategica']],
            [['IdPoliticaEstrategica'], 'exist', 'skipOnError' => true, 'targetClass' => PoliticaEstrategica::class, 'targetAttribute' => ['IdPoliticaEstrategica' => 'IdPoliticaEstrategica']],
            [['IdPei'], 'exist', 'skipOnError' => true, 'targetClass' => Pei::class, 'targetAttribute' => ['IdPei' => 'IdPei']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
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

        $id = $this->IdObjEstrategico == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdObjEstrategico;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'IdPei' => $this->IdPei,
                'IdAreaEstrategica' => $this->IdAreaEstrategica,
                'IdPoliticaEstrategica' => $this->IdPoliticaEstrategica,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdObjEstrategico', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de Objetivo Estrategico ya existe con con el Area y Politica elegidos');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdObjEstrategico' => 'Id Obj Estrategico',
            'IdAreaEstrategica' => 'Id Area Estrategica',
            'IdPoliticaEstrategica' => 'Id Politica Estrategica',
            'Codigo' => 'Codigo',
            'Objetivo' => 'Objetivo',
            'Producto' => 'Producto',
            'Indicador_Descripcion' => 'Indicador Descripcion',
            'Indicador_Formula' => 'Indicador Formula',
            'IdPei' => 'Id Pei',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listOne(string $id): ?ObjetivoEstrategico
    {
        return self::findOne(['IdObjEstrategico' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()->alias('O')
            ->select([
                'O.IdObjEstrategico',
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
            ->joinWith('pei P', true, 'INNER JOIN')
            ->joinWith('areaEstrategica Ae', true, 'INNER JOIN')
            ->joinWith('politicaEstrategica Pe', true, 'INNER JOIN')
            ->where(['!=', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'Ae.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'Pe.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['o.IdPei' => Yii::$app->contexto->getPei()]);
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
     * Gets a query for [[ObjetivosInstitucionales]].
     *
     * @return ActiveQuery
     */
    public function getObjetivosInstitucionales(): ActiveQuery
    {
        return $this->hasMany(ObjetivoInstitucional::class, ['CodigoObjEstrategico' => 'CodigoObjEstrategico']);
    }

    /**
     * Gets a query for [[Pei]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getPei(): ActiveQuery
    {
        return $this->hasOne(Pei::class, ['IdPei' => 'IdPei']);
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
     * Gets a query for [[PoliticaEstrategica]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getPoliticaEstrategica(): ActiveQuery
    {
        return $this->hasOne(PoliticaEstrategica::class, ['IdPoliticaEstrategica' => 'IdPoliticaEstrategica']);
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
