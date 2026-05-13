<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "Das".
 *
 * @property string $IdDa
 * @property string|null $Da
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property LlavePresupuestaria[] $llavesPresupuestarias
 */
class Da extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'Das';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdDa'], 'string'],
            [['Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['Da'], 'string', 'max' => 2],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdDa'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * Válida que no exista otra política activa con el mismo código y área estratégica.
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

        $id = $this->IdDa == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdDa;

        $exists = self::find()
            ->where([
                'Da' => $this->Da,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdDa', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'La unidad ya existe');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdDa' => 'Id Da',
            'Da' => 'Da',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Busca un programa específico por código, excluyendo eliminados
     *
     * @param string $id
     * @return Programa|null
     */
    public static function listOne(string $id): ?Da
    {
        return self::findOne(['IdDa' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    /**
     * Obtiene todos los programas activos (no eliminados)
     *
     * @param string $search
     * @return ActiveQuery
     */
    public static function listAll(string $search = '%%'): ActiveQuery
    {
        return self::find()
            ->select([
                'IdDa',
                'Da',
                'Descripcion',
                'CodigoEstado',
                'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'Descripcion', $search, false])
            ->orderBy(['Codigo' => SORT_ASC]);
    }

    /**
     * @param string $id
     * return string
    */
    public static function getDa(string $id): string
    {
        return self::find()
            ->where(['IdDa' => $id])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->select('Da')
            ->scalar() ?? '';
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
     * Gets query for [[LlavesPresupuestarias]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getLlavesPresupuestarias(): ActiveQuery
    {
        return $this->hasMany(LlavePresupuestaria::class, ['IdDa' => 'IdDa']);
    }
}
