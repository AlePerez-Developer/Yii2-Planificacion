<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

use common\models\Estado;
use common\models\Usuario;
use yii\db\Exception;

/**
 * This is the model class for table "PEIs".
 *
 * @property int $CodigoPei
 * @property string|null $DescripcionPei
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
            [['CodigoPei', 'FechaAprobacion', 'GestionInicio', 'GestionFin', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoPei', 'GestionInicio', 'GestionFin'], 'integer'],
            [['FechaHoraRegistro','FechaAprobacion'], 'safe'],
            [['DescripcionPei'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoPei'], 'unique'],
            [['GestionInicio'], 'unique', 'message' => 'Gestion inicio debe ser unico'],
            [['GestionFin'], 'unique', 'message' => 'Gestion fin debe ser unico'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'CodigoPei' => 'Codigo pei',
            'DescripcionPei' => 'Descripcion pei',
            'FechaAprobacion' => 'Fecha Aprobacion',
            'GestionInicio' => 'Gestion Inicio',
            'GestionFin' => 'Gestion Fin',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }


    public static function listOne($codigo): ?Pei
    {
        return self::findOne(['CodigoPei' => $codigo,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'CodigoPei',
                'DescripcionPei',
                'FechaAprobacion',
                'GestionInicio',
                'GestionFin',
                'CodigoEstado',
                'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['CodigoPei' => SORT_ASC]);
    }


    public function cambiarEstado()
    {
        $this->CodigoEstado = $this->CodigoEstado == Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
    }


    public function eliminarPei()
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
        return $this->hasMany(ObjetivoEstrategico::classname(), ['CodigoPei' => 'CodigoPei']);
    }

    /**
     * Gets query for [[CodigoEstado]].
     *
     * @return ActiveQuery
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::className(), ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return ActiveQuery
     */
    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::className(), ['CodigoUsuario' => 'CodigoUsuario']);
    }

    public function exist(): bool
    {
        $pei = Pei::find()
            ->where('(FechaAprobacion = :FechaAprobacion) or (GestionInicio = :GestionInicio) or (GestionFin = :GestionFin)',
                [':FechaAprobacion' => $this->FechaAprobacion, ':GestionInicio' => $this->GestionInicio, ':GestionFin' => $this->GestionFin]
            )
            ->andWhere(['!=','CodigoPei', $this->CodigoPei])
            ->andWhere(["CodigoEstado"=> Estado::ESTADO_VIGENTE])->all();
        if(!empty($pei)){
            return true;
        }else{
            return false;
        }
    }

    public function enUso(): bool
    {
        $Obj = ObjetivoEstrategico::find()->where(["CodigoPei" => $this->CodigoPei])->all();
        if(!empty($Obj)){
            return true;
        }else{
            return false;
        }
    }

    public function validarGestionInicio($inicioNuevo): bool
    {
        $ind = Pei::find()->alias('p')->select(['*'])
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'o.CodigoPei = p.CodigoPei')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->join('INNER JOIN','IndicadoresEstrategicosGestiones ig', 'ig.IndicadorEstrategico = i.CodigoIndicador')
            ->where('(p.CodigoPei = :pei) and (ig.Gestion < :Gestion) and (ig.Meta > 0) ',[':pei'=>$this->CodigoPei,':Gestion'=>$inicioNuevo])
            ->one();
        if (empty($ind)) {
            return true;
        } else {
            return false;
        }
    }

    public function validarGestionFin($finNuevo): bool
    {
        $ind = Pei::find()->alias('p')->select(['*'])
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'o.CodigoPei = p.CodigoPei')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->join('INNER JOIN','IndicadoresEstrategicosGestiones ig', 'ig.IndicadorEstrategico = i.CodigoIndicador')
            ->where('(p.CodigoPei = :pei) and (ig.Gestion > :Gestion) and (ig.Meta > 0)',[':pei'=>$this->CodigoPei,':Gestion'=>$finNuevo])
            ->one();
        if (empty($ind)) {
            return true;
        } else {
            return false;
        }
    }

}
