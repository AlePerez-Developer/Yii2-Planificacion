<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "Proyectos".
 *
 * @property string $IdProyecto
 * @property string $IdPrograma
 * @property string $Codigo
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Programa $programa
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property LlavePresupuestaria[] $llavesPresupuestarias
 */
class Proyecto extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'Proyectos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdPrograma', 'Codigo', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdProyecto', 'IdPrograma'], 'string', 'max' => 36],
            [['Codigo'], 'string', 'min' => 3, 'max' => 20],
            [['Codigo'], 'match', 'pattern' => '/^\d+$/', 'message' => 'El código solo puede contener números.'],
            [['Descripcion'], 'string', 'max' => 500],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['IdPrograma'], 'exist', 'skipOnError' => true, 'targetClass' => Programa::class, 'targetAttribute' => ['IdPrograma' => 'IdPrograma']],
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

        $id = $this->IdProyecto == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdProyecto;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'IdPrograma' => $this->IdPrograma,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdProyecto', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de proyecto ya existe con programa elegido');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdProyecto' => 'Id Proyecto',
            'IdPrograma' => 'Id Programa',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Busca un proyecto específico por código, excluyendo eliminados
     *
     * @param string $id
     * @return Programa|null
     */
    public static function listOne(string $id): ?Proyecto
    {
        return self::findOne(['IdProyecto' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    /**
     * Obtiene todos los proyectos activos (no eliminados)
     *
     * @param string $search
     * @return ActiveQuery
     */
    public static function listAll(string $search = '%%'): ActiveQuery
    {
        return self::find()->alias('P')
            ->select([
                'P.IdProyecto',
                'P.Codigo',
                'P.Descripcion',
                'P.CodigoEstado',
                'P.CodigoUsuario',
                'Prg.IdPrograma',
            ])
            ->joinWith('programa Prg', true, 'INNER JOIN')
            ->where(['!=', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'P.Descripcion', $search, false])
            ->andWhere(['!=', 'Prg.CodigoEstado', Estado::ESTADO_ELIMINADO]);
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
     * Gets a query for [[LlavesPresupuestarias]].
     *
     * @return ActiveQuery
     */
    public function getLlavesPresupuestarias(): ActiveQuery
    {
        return $this->hasMany(LlavePresupuestaria::class, ['IdProyecto' => 'IdProyecto']);
    }

    /**
     * Gets a query for [[Programa]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getPrograma(): ActiveQuery
    {
        return $this->hasOne(Programa::class, ['IdPrograma' => 'IdPrograma']);
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
