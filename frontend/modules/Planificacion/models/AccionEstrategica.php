<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "AccionesEstrategicas".
 *
 * @property string $IdAccionEstrategica
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */

class AccionEstrategica extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'AccionesEstrategicas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdAccionEstrategica'], 'string'],
            [['Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdAccionEstrategica'], 'unique'],
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
            'IdAccionEstrategica' => 'Id Accion Estrategica',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Busca una Accion específica por código, excluyendo eliminados
     *
     * @param string $id
     * @return Programa|null
     */
    public static function listOne(string $id): ?AccionEstrategica
    {
        return self::findOne(['IdAccionEstrategica' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    /**
     * Obtiene todas las actividades activas (no eliminados)
     *
     * @param string $search
     * @return ActiveQuery
     */
    public static function listAll(string $search = '%%'): ActiveQuery
    {
        return self::find()->alias('A')
            ->select([
                'A.IdAccionEstrategica',
                'A.Descripcion',
                'A.CodigoEstado',
                'A.CodigoUsuario',
            ])
            ->where(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'A.Descripcion', $search, false]);
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
     * Gets a query for [[LlavesPresupuestarias]].
     *
     * @return ActiveQuery
     */
    public function getIndicadoresEstrategicos(): ActiveQuery
    {
        return $this->hasMany(IndicadorEstrategico::class, ['IdAccionEstrategica' => 'IdAccionEstrategica']);
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
     */
    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }
}
