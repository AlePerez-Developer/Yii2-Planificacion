<?php

namespace app\modules\PlanificacionCH\dao;

use Yii;

class FacultadesDao
{
    /*=============================================
    LISTA FACULTADES
    =============================================*/
    static public function listaFacultades()
    {
        $dbRRHH = Yii::$app->dbAcademica;
        $consulta = "SELECT fac.CodigoFacultad, fac.NombreFacultad
                     FROM Facultades fac
                     WHERE fac.CodigoFacultad not in('CE')
ORDER BY fac.NombreFacultad ";
        $instruccion = $dbRRHH->createCommand($consulta);
        $lector = $instruccion->query();
        $facultades = [];
        while ($facultad = $lector->readObject(FacultadObj::className(), [])) {
            $facultades[] = $facultad;
        }
        return $facultades;
    }
}