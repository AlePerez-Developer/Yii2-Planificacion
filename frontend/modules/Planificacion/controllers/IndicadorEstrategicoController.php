<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\IndicadorEstrategicoDao;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\models\CategoriaIndicador;
use app\modules\Planificacion\models\IndicadorUnidad;
use app\modules\Planificacion\models\TipoIndicador;
use app\modules\Planificacion\models\TipoResultado;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Estado;
use yii\web\Controller;
use Mpdf\MpdfException;
use Throwable;
use Mpdf\Mpdf;
use Yii;

class IndicadorEstrategicoController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $objsEstrategicos = ObjetivoEstrategico::find()->alias('oe')
            ->select(['oe.CodigoObjEstrategico','oe.CodigoObjetivo','oe.Objetivo'])
            ->where(['oe.CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->asArray()->all();
        $tiposResultados = TipoResultado::find()->where(['CodigoEstado'=> Estado::ESTADO_VIGENTE])->all();
        $tiposIndicadores = TipoIndicador::find()->where(['CodigoEstado'=> Estado::ESTADO_VIGENTE])->all();
        $categoriasIndicadores = CategoriaIndicador::find()->where(['CodigoEstado'=> Estado::ESTADO_VIGENTE])->all();
        $indicadoresUnidades = IndicadorUnidad::find()->where(['CodigoEstado'=> Estado::ESTADO_VIGENTE])->all();
        return $this->render('IndicadoresEstrategicos',[
            'objsEstrategicos' => $objsEstrategicos,
            'Resultados'=> $tiposResultados,
            'Tipos'=> $tiposIndicadores,
            'Categorias'=> $categoriasIndicadores,
            'Unidades'=> $indicadoresUnidades
        ]);
    }

    public function actionListarIndicadoresEstrategicos()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $indicadores = IndicadorEstrategico::find()->alias('I')->select([
                'I.CodigoIndicador', 'I.Codigo', 'I.Meta', 'I.Descripcion', 'I.ObjetivoEstrategico', 'I.Resultado', 'I.TipoIndicador', 'I.Categoria', 'I.Unidad', 'I.CodigoEstado',
                'I.CodigoUsuario',
                'Oe.CodigoObjetivo as CodigoObjetivo', 'Oe.Objetivo as ObjetivoEstrategico',
                'Tr.Descripcion as ResultadoDescripcion',
                'Ti.Descripcion as TipoDescripcion',
                'Ci.Descripcion as CategoriaDescripcion',
                'U.Descripcion as UnidadDescripcion',
                '(sum(isnull(ig.Meta,0))) as Programado',
                'I.Meta - (sum(isnull(ig.Meta,0))) as Diff'
            ])
                ->join('INNER JOIN','ObjetivosEstrategicos Oe', 'I.ObjetivoEstrategico = Oe.CodigoObjEstrategico')
                ->join('INNER JOIN','PEIs p', 'oe.CodigoPei = p.CodigoPei')
                ->join('INNER JOIN','TiposResultados Tr', 'I.Resultado = Tr.CodigoTipo')
                ->join('INNER JOIN','TiposIndicadores Ti', 'I.TipoIndicador = Ti.CodigoTipo')
                ->join('INNER JOIN','CategoriasIndicadores Ci', 'I.Categoria = Ci.CodigoCategoria')
                ->join('INNER JOIN','IndicadoresUnidades U', 'I.Unidad = U.CodigoTipo')
                ->join('LEFT JOIN', 'IndicadoresEstrategicosGestiones ig', 'ig.IndicadorEstrategico = i.CodigoIndicador')
                ->where(['!=', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])->andWhere(['!=', 'Oe.CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->andWhere(['!=', 'Tr.CodigoEstado', Estado::ESTADO_ELIMINADO])->andWhere(['!=', 'Ti.CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->andWhere(['!=', 'Ci.CodigoEstado', Estado::ESTADO_ELIMINADO])->andWhere(['!=', 'U.CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->andWhere(['p.CodigoPei'=>1/*por ahora!!!*/])
                ->groupBy('I.CodigoIndicador, I.Codigo, I.Meta, I.Descripcion, I.ObjetivoEstrategico, I.Resultado,I.TipoIndicador, I.Categoria, I.Unidad,I.CodigoEstado,I.FechaHoraRegistro,I.CodigoUsuario,
                                   Oe.CodigoObjetivo, Oe.Objetivo,
                                   Tr.Descripcion, Ti.Descripcion, Ci.Descripcion, U.Descripcion')
                ->orderBy('I.Codigo')
                ->asArray()->all();
            return json_encode($indicadores);
        } else
            return 'ERROR_CABECERA';
    }

    public function actionGuardarIndicadorEstrategico()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoObjetivoEstrategico"])
            && isset($_POST["codigoIndicador"]) && isset($_POST["metaIndicador"])  && isset($_POST["descripcion"])
            && isset($_POST["tipoResultado"]) && isset($_POST["tipoIndicador"]) && isset($_POST["categoriaIndicador"]) && isset($_POST["tipoUnidad"]))
        ) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $indicador = new IndicadorEstrategico();
        $indicador->CodigoIndicador = IndicadorEstrategicoDao::GenerarCodigoIndicadorEstrategico();
        $indicador->Codigo = intval($_POST["codigoIndicador"]);
        $indicador->Meta = intval($_POST["metaIndicador"]);
        $indicador->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
        $indicador->ObjetivoEstrategico = intval($_POST["codigoObjetivoEstrategico"]);
        $indicador->Resultado = intval($_POST["tipoResultado"]);
        $indicador->TipoIndicador = intval($_POST["tipoIndicador"]);
        $indicador->Categoria = intval($_POST["categoriaIndicador"]);
        $indicador->Unidad = intval($_POST["tipoUnidad"]);
        $indicador->CodigoEstado = Estado::ESTADO_VIGENTE;
        $indicador->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        if ($indicador->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$indicador->validate()){
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }

        $transaction = IndicadorEstrategico::getDb()->beginTransaction();
        try {
            if (!$indicador->save()){
                $transaction->rollBack();
                return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
            }
            if (!$indicador->generarProgramacion()){
                $transaction->rollBack();
                return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
            }

            $transaction->commit();
            return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);

        } catch(\Exception|Throwable $e) {
            $transaction->rollBack();
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionCambiarEstadoIndicadorEstrategico()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!isset($_POST["codigoIndicadorEstrategico"])) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $indicador = IndicadorEstrategico::findOne($_POST["codigoIndicadorEstrategico"]);

        if (!$indicador){
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        ($indicador->CodigoEstado == Estado::ESTADO_VIGENTE)?$indicador->CodigoEstado = Estado::ESTADO_CADUCO: $indicador->CodigoEstado = Estado::ESTADO_VIGENTE;

        if ($indicador->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionEliminarIndicadorEstrategico()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoIndicadorEstrategico"]) && $_POST["codigoIndicadorEstrategico"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $indicador = IndicadorEstrategico::findOne($_POST["codigoIndicadorEstrategico"]);

        if (!$indicador) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }
        if ($indicador->enUso()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EN_USO']]);
        }

        $indicador->CodigoEstado = Estado::ESTADO_ELIMINADO;

        if ($indicador->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionBuscarIndicadorEstrategico()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoIndicadorEstrategico"]) && $_POST["codigoIndicadorEstrategico"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $indicador = IndicadorEstrategico::find()->alias('I')->select([
            'I.CodigoIndicador','I.Codigo','I.Meta','I.Descripcion',
            'I.Resultado','tr.Descripcion as ResultadoDescripcion',
            'I.TipoIndicador', 'ti.Descripcion as TipoIndicadorDescripcion',
            'I.Categoria', 'ci.Descripcion as CategoriaDescripcion',
            'I.Unidad', 'iu.Descripcion as UnidadDescripcion',
            'I.ObjetivoEstrategico', 'o.CodigoObjetivo', 'o.Objetivo', 'sum(isnull(ieg.Meta,0)) as metaProgramada'
        ])
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->join('INNER JOIN','TiposResultados tr', 'i.Resultado = tr.CodigoTipo')
            ->join('INNER JOIN','TiposIndicadores ti', 'i.TipoIndicador = ti.CodigoTipo')
            ->join('INNER JOIN','CategoriasIndicadores ci', 'i.Categoria = ci.CodigoCategoria')
            ->join('INNER JOIN','IndicadoresUnidades iu', 'i.Unidad = iu.CodigoTipo')
            ->join('left JOIN','IndicadoresEstrategicosGestiones ieg', 'ieg.IndicadorEstrategico = i.CodigoIndicador')
            ->where(['I.CodigoIndicador' => $_POST["codigoIndicadorEstrategico"]])
            ->groupBy('I.CodigoIndicador,I.Codigo,I.Meta,I.Descripcion,I.Resultado,tr.Descripcion,I.TipoIndicador,ti.Descripcion,I.Categoria, ci.Descripcion,
                                       I.Unidad, iu.Descripcion,I.ObjetivoEstrategico, o.CodigoObjetivo, o.Objetivo')
            ->asArray()->one();

        if (!$indicador) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }
        if (!IndicadorEstrategico::findOne($_POST["codigoIndicadorEstrategico"])->generarProgramacion()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'ind' => $indicador]);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionActualizarIndicadorEstrategico()
    {
        if(!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoIndicadorEstrategico"]) && $_POST["codigoIndicadorEstrategico"] != ""
            && isset($_POST["codigoObjetivoEstrategico"])
            && isset($_POST["codigoIndicador"]) && isset($_POST["metaIndicador"]) && isset($_POST["descripcion"])
            && isset($_POST["tipoResultado"]) && isset($_POST["tipoIndicador"]) && isset($_POST["categoriaIndicador"]) && isset($_POST["tipoUnidad"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $indicador = IndicadorEstrategico::findOne($_POST["codigoIndicadorEstrategico"]);

        if (!$indicador) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        $indicador->Codigo = intval($_POST["codigoIndicador"]);
        $indicador->Meta = intval($_POST["metaIndicador"]);
        $indicador->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
        $indicador->ObjetivoEstrategico = intval($_POST["codigoObjetivoEstrategico"]);
        $indicador->Resultado = intval($_POST["tipoResultado"]);
        $indicador->TipoIndicador = intval($_POST["tipoIndicador"]);
        $indicador->Categoria = intval($_POST["categoriaIndicador"]);
        $indicador->Unidad = intval($_POST["tipoUnidad"]);

        if ($indicador->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$indicador->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }
        if ($indicador->update() === false)
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionVerificarCodigo()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return false;
        }
        if (!isset($_POST["codigo"])) {
            return false;
        }

        $indicadorEstrategico = IndicadorEstrategico::find()
            ->where(['Codigo' => $_POST["codigo"], 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','CodigoIndicador',$_POST["indicadorEstrategico"]])
            ->one();

        if ($indicadorEstrategico) {
            return false;
        }

        return true;
    }

    /**
     * @throws MpdfException
     */
    public function actionReporte()
    {
        $mpdf = new Mpdf();
        $mpdf->SetMargins(0, 0,32);
        /*$mpdf->SetHTMLHeader('
            <table style="width: 100%" >
                <tr>
                    <td width="7%" style="border-right: 1px solid black" >
                        <img src="img/EscudoPNG.png" width="7%">
                    </td>
                    <td width="25%" style="font-size: 9px">Universidad Mayor Real y Pontificia de San Francisco Xavier de Chuquisaca</td>
                    <td width="53%" style="text-align: center; vertical-align: bottom; border-style: hidden" >Indicadores Estrategicos</td>
                    <td width="15%" style="text-align: center" >
                        <img src="img/logo400.png" width="15%">
                    </td>
                </tr>
            </table>
            <hr>
        ');
        $mpdf->SetHTMLFooter('
            <hr>
            <table width="100%">
                <tr>
                    <td width="33%"  style="font-size: 9px">'. Yii::$app->user->identity->Login .'('.Yii::$app->user->identity->CodigoUsuario.')'  .'</td>
                    <td width="33%"  style="font-size: 9px" align="center">{PAGENO}/{nbpg}</td>
                    <td width="33%" style="text-align: right; font-size: 9px">{DATE j-m-Y h:i:s}</td>
                </tr>
            </table>'
        );

        $a = '<table  width="100%" style="border: none; border-collapse: collapse "> <tr>' ;
        $a .= '<thead >';
        $a .=   '<tr>';
        $a .=       '<th width="10%" style="border-bottom: 1px solid black">cabecera</th>';
        $a .=       '<th width="50%" style="border-bottom: 1px solid black"> cabecera</th>';
        $a .=       '<th width="40%" style="border-bottom: 1px solid black">cabecera </th>';
        $a .=   '</tr>';
        $a .= ' </thead>';
        $a .= ' <tbody>';



        $a .= ' </tbody>';
        $a .= '</table>';


        $mpdf->WriteHTML($a);*/

        $mpdf->Output();
    }
}