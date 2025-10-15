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
            [['GestionInicio'], 'unique', 'message' => 'Gestion inicio debe ser unico'],
            [['GestionFin'], 'unique', 'message' => 'Gestion fin debe ser unico'],
            [['GestionInicio'], 'number', 'min' => 2000, 'tooSmall' => 'la Gestion de inicio debe ser mayor al año 2000'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
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
        return $this->hasMany(ObjetivoEstrategico::class, ['pei_id' => 'IdPei']);
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