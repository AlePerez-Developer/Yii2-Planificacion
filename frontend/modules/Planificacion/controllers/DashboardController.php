<?php

namespace app\modules\Planificacion\controllers;

use yii\web\Controller;

class DashboardController extends Controller
{
    public function actionIndex(): string
    {
        // Renderiza la vista 'index' que está dentro de la carpeta del módulo
        return $this->render('index');
    }

}