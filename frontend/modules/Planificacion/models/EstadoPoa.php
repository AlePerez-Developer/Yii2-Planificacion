<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "EstadosPOA".
 *
 * @property int $CodigoEstadoPOA
 * @property string $Descripcion
 * @property string $Abreviacion
 * @property int $EtapaActual
 * @property int $EtapaPredeterminada
 * @property int $Orden
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class EstadoPoa extends ActiveRecord
{
	public static function tableName(): string
	{
		return 'EstadosPOA';
	}

	public static function primaryKey(): array
	{
		return ['CodigoEstadoPOA'];
	}

	public function rules(): array
	{
		return [
			[
				[
					'Descripcion',
					'Abreviacion',
					'EtapaActual',
					'EtapaPredeterminada',
					'Orden',
					'CodigoEstado',
					'CodigoUsuario',
				],
				'required'
			],
			[['CodigoEstadoPOA', 'EtapaActual', 'EtapaPredeterminada', 'Orden'], 'integer'],
			[['FechaHoraRegistro'], 'safe'],
			[['Descripcion'], 'string', 'max' => 200],
			[['Abreviacion'], 'string', 'max' => 3],
			[['CodigoEstado'], 'string', 'max' => 1],
			[['CodigoUsuario'], 'string', 'max' => 3],
			[['Descripcion', 'Abreviacion'], 'trim'],
			[['CodigoEstadoPOA'], 'unique'],
			[
				['CodigoEstado'],
				'exist',
				'skipOnError' => true,
				'targetClass' => Estado::class,
				'targetAttribute' => ['CodigoEstado' => 'CodigoEstado'],
			],
			[
				['CodigoUsuario'],
				'exist',
				'skipOnError' => true,
				'targetClass' => Usuario::class,
				'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario'],
			],
		];
	}

	public function attributeLabels(): array
	{
		return [
			'CodigoEstadoPOA' => 'Código',
			'Descripcion' => 'Descripción',
			'Abreviacion' => 'Abreviación',
			'EtapaActual' => 'Etapa Actual',
			'EtapaPredeterminada' => 'Etapa Predeterminada',
			'Orden' => 'Orden',
			'CodigoEstado' => 'Estado',
			'FechaHoraRegistro' => 'Fecha Registro',
			'CodigoUsuario' => 'Usuario',
		];
	}

	public static function listAll(): ActiveQuery
	{
		return self::find()
			->select([
				'CodigoEstadoPOA',
				'Descripcion',
				'Abreviacion',
				'EtapaActual',
				'EtapaPredeterminada',
				'Orden',
				'CodigoEstado',
				'CodigoUsuario',
				'FechaHoraRegistro',
			])
			->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
			->orderBy(['Orden' => SORT_ASC, 'CodigoEstadoPOA' => SORT_ASC]);
	}

	public static function listOne(int $codigoEstadoPoa): ?self
	{
		return self::find()
			->where(['CodigoEstadoPOA' => $codigoEstadoPoa])
			->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
			->one();
	}

	public function cambiarEstado(): void
	{
		$this->CodigoEstado = $this->CodigoEstado === Estado::ESTADO_VIGENTE
			? Estado::ESTADO_CADUCO
			: Estado::ESTADO_VIGENTE;
	}

	public function eliminar(): void
	{
		$this->CodigoEstado = Estado::ESTADO_ELIMINADO;
	}

	public function exist(): bool
	{
		return self::find()
			->where([
				'or',
				['Descripcion' => $this->Descripcion],
				['Abreviacion' => $this->Abreviacion],
			])
			->andWhere(['!=', 'CodigoEstadoPOA', $this->CodigoEstadoPOA])
			->andWhere(['CodigoEstado' => Estado::ESTADO_VIGENTE])
			->exists();
	}

	public function enUso(): bool
	{
		return false;
	}

	public function getCodigoEstado(): ActiveQuery
	{
		return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
	}

	public function getCodigoUsuario(): ActiveQuery
	{
		return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
	}
}
