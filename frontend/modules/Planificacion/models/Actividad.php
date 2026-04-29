<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "Actividades".
 *
 * @property string $IdActividad
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
class Actividad extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'Actividades';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdPrograma', 'Codigo', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdActividad', 'IdPrograma'], 'string', 'max' => 36],
            [['Codigo'],'match','pattern' => '/^\d{3}$/','message' => 'Debe contener exactamente 3 dígitos (ej: 023).'],
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

        $id = $this->IdActividad == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdActividad;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'IdPrograma' => $this->IdPrograma,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdActividad', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de actividad ya existe con programa elegido');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdActividad' => 'Id Actividad',
            'IDPrograma' => 'Id Programa',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Busca una actividad específica por código, excluyendo eliminados
     *
     * @param string $id
     * @return Programa|null
     */
    public static function listOne(string $id): ?Actividad
    {
        return self::findOne(['IdActividad' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
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
                'A.IdActividad',
                'A.Codigo',
                'A.Descripcion',
                'A.CodigoEstado',
                'A.CodigoUsuario',
                'Prg.IdPrograma',
            ])
            ->joinWith('programa Prg', true, 'INNER JOIN')
            ->where(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'A.Descripcion', $search, false])
            ->andWhere(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO]);
    }

    /**
     * Obtiene todas las actividades activas (no eliminados)
     *
     * @param string $search
     * @return ActiveQuery
     */
    public static function listAllbyPrograma(string $idPrograma, string $search = '%%'): ActiveQuery
    {
        return self::find()->alias('A')
            ->select([
                'A.IdActividad',
                'A.Codigo',
                'A.Descripcion',
                'A.CodigoEstado',
                'A.CodigoUsuario',
                'Prg.IdPrograma',
            ])
            ->joinWith('programa Prg', true, 'INNER JOIN')
            ->where(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andwhere(['like', 'A.Descripcion', $search, false])
            ->andwhere(['A.IdPrograma' => $idPrograma])
            ->andWhere(['!=', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO]);
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
        return $this->hasMany(LlavePresupuestaria::class, ['IdActividad' => 'IdActividad']);
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
