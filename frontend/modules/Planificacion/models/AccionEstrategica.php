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
 * @property int $Codigo
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
            [['Codigo', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Codigo'], 'integer', 'message' => 'El código debe ser un valor numérico.'],
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
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Busca una Accion específica por id, excluyendo eliminados
     *
     * @param string $id
     * @return AccionEstrategica|null
     */
    public static function listOne(string $id): ?AccionEstrategica
    {
        return self::findOne(['IdAccionEstrategica' => $id, ['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO]]);
    }

    /**
     * Obtiene todas las acciones activas (no eliminadas)
     *
     * @param string $search
     * @return ActiveQuery
     */
    public static function listAll(string $search = '%%'): ActiveQuery
    {
        return self::find()->alias('A')
            ->select([
                'A.IdAccionEstrategica',
                'A.Codigo',
                'A.Descripcion',
                'A.CodigoEstado',
                'A.CodigoUsuario',
            ])
            ->where(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'A.Descripcion', $search, false])
            ->orderBy(['A.Codigo' => SORT_ASC]);
    }

    /**
     * Alterna el estado del modelo V/C, o fuerza un estado si se envía.
     *
     * @param string|null $codigoEstado
     * @return void
     */
    public function cambiarEstado(?string $codigoEstado = null): void
    {
        if ($codigoEstado === Estado::ESTADO_VIGENTE || $codigoEstado === Estado::ESTADO_CADUCO) {
            $this->CodigoEstado = $codigoEstado;
            return;
        }

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
     * Gets a query for [[IndicadoresEstrategicos]].
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
