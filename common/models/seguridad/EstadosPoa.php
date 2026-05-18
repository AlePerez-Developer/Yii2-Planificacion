<?php

namespace common\models\seguridad;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;
use common\models\Usuario;


/**
 * This is the model class for table "EstadosPoa".
 *
 * @property string $IdEstadoPoa
 * @property string $Codigo
 * @property string $Descripcion
 * @property int $EtapaPredeterminada
 * @property int $Orden
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Usuario $codigoUsuario
 * @property Estado $codigoEstado
 * @property UsuarioContextoActivo[] $usuarioContextoActivos
 * @property UsuarioDaGestionEstado[] $usuarioDaGestionEstados
 * @property UsuarioLlaveMenuPermiso[] $usuarioLlaveMenuPermisos
 */
class EstadosPoa extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.EstadosPoa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdEstadoPoa'], 'string', 'max' => 36],
            [['Codigo', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['EtapaPredeterminada', 'Orden'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'string', 'max' => 10],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Codigo'], 'unique'],
            [['IdEstadoPoa'], 'unique'],
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
            'IdEstadoPoa' => 'Id Estado Poa',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'EtapaPredeterminada' => 'Etapa Predeterminada',
            'Orden' => 'Orden',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Gets query for [[UsuarioContextoActivos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuarioContextoActivos(): ActiveQuery
    {
        return $this->hasMany(UsuarioContextoActivo::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
    }

    /**
     * Gets a query for [[UsuarioDaGestionEstados]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuarioDaGestionEstados(): ActiveQuery
    {
        return $this->hasMany(UsuarioDaGestionEstado::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
    }

    /**
     * Gets a query for [[UsuarioLlaveMenuPermisos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuarioLlaveMenuPermisos(): ActiveQuery
    {
        return $this->hasMany(UsuarioLlaveMenuPermiso::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
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
     * Gets a query for [[CodigoEstado]].
     *
     * @return ActiveQuery
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }
}
