<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\Pei;
use yii\db\StaleObjectException;
use yii\db\Exception;
use Throwable;
use Yii;

class PeiDao
{
    static function enUso(Pei $pei): bool
    {
        return $pei->getObjetivosEstrategicos()->exists();
    }

    /**
     * @throws Exception|ValidationException
     */
    static function generarGestionesPei(Pei $pei): array
    {
        for ($i = $pei->GestionInicio; $i <= $pei->GestionFin; $i++) {
            $gestion = new PeiGestion();
            $gestion->IdPei = $pei->IdPei;
            $gestion->Gestion  = $i;
            $gestion->CodigoUsuario = $pei->CodigoUsuario;

            if (!$gestion->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$gestion->getErrors(),500);
            }

            if (!$gestion->save(false)) {
                Yii::error("Error al guardar el cambio de estado del PEI $gestion->IdPei", __METHOD__);
                throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$gestion->getErrors(),500);
            }
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    static function eliminarGestionesPei(Pei $pei): void
    {
        PeiGestion::deleteAll(['IdPei' => $pei->IdPei]);
    }

    static function validarGestionInicio(string $idPei, $inicioNuevo): bool
    {
        return !Pei::find()->alias('P')->select([
            'P.IdPei',
            'peiGestion.IdGestion',
            'IndicadorEstrategicoProgramacionGestion.IdProgramacionGestion',
        ])
            ->joinWith('peiGestion.gestionProgramacion', true, 'INNER JOIN')
            ->where('p.IdPei = :pei',[':pei' => $idPei])
            ->andWhere('peiGestion.Gestion < :Gestion',[':Gestion' => $inicioNuevo])
            ->andWhere('IndicadorEstrategicoProgramacionGestion.MetaProgramada > 0')
            ->exists();
    }

    static function validarGestionFin(string $idPei, $finNuevo): bool
    {
        return !Pei::find()->alias('P')->select([
            'P.IdPei',
            'peiGestion.IdGestion',
            'IndicadorEstrategicoProgramacionGestion.IdProgramacionGestion',
        ])
            ->joinWith('peiGestion.gestionProgramacion', true, 'INNER JOIN')
            ->where('p.IdPei = :pei',[':pei' => $idPei])
            ->andWhere('peiGestion.Gestion > :Gestion',[':Gestion' => $finNuevo])
            ->andWhere('IndicadorEstrategicoProgramacionGestion.MetaProgramada > 0')
            ->exists();
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    static function regularizarProgramacionIndicadoresFin(Pei $modelo, int $gestionFin, string $accion): void
    {
        switch ($accion) {
            case 'add':{
                for ($i = $modelo->getOldAttribute('GestionFin'); $i <= $gestionFin; $i++) {
                    $model = new PeiGestion();
                    $model->IdPei = $modelo->IdPei;
                    $model->Gestion  = $i;
                    $model->CodigoUsuario = $modelo->CodigoUsuario;

                    if (!$model->validate()) {
                        throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$model->getErrors(),500);
                    }

                    if (!$model->save(false)) {
                        Yii::error("Error al guardar el cambio de estado del PEI $model->IdPei", __METHOD__);
                        throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$model->getErrors(),500);
                    }
                }
            }
            case 'del':{
                $model = PeiGestion::find()->select('*')->alias('G')
                    ->where('(G.IdPei = :pei) and (G.Gestion > :Gestion)',[':pei'=>$modelo->IdPei,':Gestion'=>$gestionFin])
                    ->all();
                foreach ($model as $programacion){
                    if (!$programacion->delete()){
                        throw new Exception("No se pudo eliminar la programación", 500);
                    }
                }
            }
                break;
        }



        /*$model = PeiGestion::find()->select('*')->alias('G')
            ->where('(G.IdPei = :pei) and (G.Gestion > :Gestion)',[':pei'=>$idPei,':Gestion'=>$gestionFin])
            ->all();
        foreach ($model as $programacion){
            if (!$programacion->delete()){
                throw new Exception("No se pudo eliminar la programación", 500);
            }
        }*/
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    static function regularizarProgramacionIndicadoresInicio(Pei $modelo, int $gestionInicio, string $accion): void
    {
        switch ($accion) {
            case 'add':{
                for ($i = $gestionInicio; $i <= $modelo->getOldAttribute('GestionInicio'); $i++) {
                    $model = new PeiGestion();
                    $model->IdPei = $modelo->IdPei;
                    $model->Gestion  = $i;
                    $model->CodigoUsuario = $modelo->CodigoUsuario;

                    if (!$model->validate()) {
                        throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$model->getErrors(),500);
                    }

                    if (!$model->save(false)) {
                        Yii::error("Error al guardar el cambio de estado del PEI $model->IdPei", __METHOD__);
                        throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$model->getErrors(),500);
                    }
                }
            }
            case 'del':{
                $model = PeiGestion::find()->select('*')->alias('G')
                    ->where('(G.IdPei = :pei) and (G.Gestion < :Gestion)',[':pei'=>$modelo->IdPei,':Gestion'=>$gestionInicio])
                    ->all();
                foreach ($model as $programacion){
                    if (!$programacion->delete()){
                        throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$programacion->getErrors(),500);
                    }
                }
            }
            break;
        }
    }
}