<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "PEIs".
 *
 * @property string $IdPei
 * @property string|null $Descripcion
 * @property string $FechaAprobacion
 * @property int $GestionInicio
 * @property int $GestionFin
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ObjetivoEstrategico[] $objetivosEstrategicos
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class Pei extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'PEIs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['FechaAprobacion', 'GestionInicio', 'GestionFin', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['GestionInicio', 'GestionFin'], 'integer'],
            [['IdPei'], 'safe'],
            [['FechaHoraRegistro','FechaAprobacion'], 'safe'],
            [['IdPei'], 'string', 'max' => 36],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdPei'], 'unique'],
            [['GestionInicio'], 'validateUniqueInicio', 'skipOnError' => true],
            [['GestionFin'], 'validateUniqueFin', 'skipOnError' => true],
            [['GestionInicio'], 'number', 'min' => 2000, 'tooSmall' => 'la Gestion de inicio debe ser mayor al año 2000'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * Valida que la gestion de inicio sea unica en los registros vigentes.
     *
     * @param string $attribute
     * @used-by rules()
     * @noinspection PhpUnused
     */
    public function validateUniqueInicio(string $attribute): void
    {
        if ($this->CodigoEstado !== 'V') {
            return;
        }

        $id = $this->IdPei == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdPei;

        $exists = self::find()
            ->where([
                'GestionInicio' => $this->GestionInicio,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdPei', $id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'Gestion inicio debe ser unico');
        }
    }

    /**
     * Valida que la gestion de fin sea unica en los registros vigentes.
     *
     * @param string $attribute
     * @used-by rules()
     * @noinspection PhpUnused
     */
    public function validateUniqueFin(string $attribute): void
    {
        if ($this->CodigoEstado !== 'V') {
            return;
        }

        $id = $this->IdPei == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdPei;

        $exists = self::find()
            ->where([
                'GestionFin' => $this->GestionFin,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdPei', $id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'Gestion fin debe ser unico');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdPei' => 'Identificador de pei',
            'Descripcion' => 'Descripcion pei',
            'FechaAprobacion' => 'Fecha Aprobacion',
            'GestionInicio' => 'Gestion Inicio',
            'GestionFin' => 'Gestion Fin',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }


    public static function listOne(string $id): ?Pei
    {
        return self::findOne(['IdPei' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdPei',
                'Descripcion',
                'FechaAprobacion',
                'GestionInicio',
                'GestionFin',
                'CodigoEstado',
                'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['IdPei' => SORT_ASC]);
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
     * Gets query for [[ObjetivosEstrategicos]].
     *
     * @return ActiveQuery
     */
    public function getObjetivosEstrategicos(): ActiveQuery
    {
        return $this->hasMany(ObjetivoEstrategico::class, ['IdPei' => 'IdPei']);
    }

    /**
     * Gets query for [[PeiGestion]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getPeiGestion(): ActiveQuery
    {
        return $this->hasMany(PeiGestion::class, ['IdPei' => 'IdPei']);
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