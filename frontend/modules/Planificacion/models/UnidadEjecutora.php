<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;
use yii\db\Expression;

/**
 * This is the model class for table "UnidadesEjecutoras".
 *
 * @property string $IdUnidadEjecutora
 * @property string $IdDa
 * @property string $Ue
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property Da $das
 */
class UnidadEjecutora extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'UnidadesEjecutoras';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUnidadEjecutora', 'IdDa'], 'string', 'max' => 36],
            [['IdDa', 'Ue', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Ue'], 'validateUniqueActiva', 'skipOnError' => true],
            [['FechaHoraRegistro'], 'safe'],
            [['Ue', 'CodigoUsuario'], 'string', 'max' => 3],
            [['Ue'], 'match', 'pattern' => '/^\d{3}$/', 'message' => 'La Unidad Ejecutora debe contener exactamente 3 dígitos numéricos.'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUnidadEjecutora'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdDa'], 'exist', 'skipOnError' => true, 'targetClass' => Da::class, 'targetAttribute' => ['IdDa' => 'IdDa']],
        ];
    }

    /**
     * Válida que no exista otra Ue activa con el mismo IdDa
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

        $id = $this->IdUnidadEjecutora == null ? '00000000-0000-0000-0000-000000000000' : $this->IdUnidadEjecutora;

        $exists = self::find()
            ->where([
                'Ue' => $this->Ue,
                'IdDa' => $this->IdDa,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdUnidadEjecutora', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'La Ue ya existe en la Da');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdUnidadEjecutora' => 'Id Unidad Ejecutora',
            'idDa' => 'Id Da',
            'Ue' => 'Ue',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
            'UeTemp' => 'Ue Temp',
        ];
    }

    /**
     * Busca un programa específico por código, excluyendo eliminados
     *
     * @param string $id
     * @return UnidadEjecutora|null
     */
    public static function listOne(string $id): ?UnidadEjecutora
    {
        return self::findOne(['IdUnidadEjecutora' => $id, ['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO]]);
    }

    /**
     * Obtiene todas las unidades ejecutoras activas (no eliminadas)
     *
     * @param string $search
     * @return ActiveQuery
     */
    public static function listAll(string $search = ""): ActiveQuery
    {
        return self::find()->alias('u')
            ->select([
                'u.IdUnidadEjecutora',
                'd.IdDa',
                'u.Ue',
                'u.Descripcion',
                'u.CodigoEstado',
                'u.CodigoUsuario',
                'd.Da',
                new Expression("CONCAT(d.Da, '-', Ue) AS [[Compuesto]]")
            ])
            ->joinWith('das d', true, 'INNER JOIN')
            ->where(['!=', 'u.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'u.Descripcion', $search])
            ->groupBy(['u.IdUnidadEjecutora', 'd.IdDa', 'd.Da', 'u.Ue', 'u.Descripcion', 'u.CodigoEstado', 'u.CodigoUsuario'])
            ->orderBy([
                'd.Da' => SORT_ASC,
                'Ue' => SORT_ASC,
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
     */
    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[Das]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getDas(): ActiveQuery
    {
        return $this->hasOne(Da::class, ['IdDa' => 'IdDa']);
    }

    /**
     * Gets query for [[LlavesPresupuestarias]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getLlavesPresupuestarias(): ActiveQuery
    {
        return $this->hasMany(LlavePresupuestaria::class, ['IdUnidadEjecutora' => 'IdUnidadEjecutora']);
    }
}
