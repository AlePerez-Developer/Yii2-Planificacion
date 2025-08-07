<?php
namespace app\controllers;

use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use Exception;
use Throwable;
use Yii;

class BaseController extends Controller
{
    protected array $accionesSinValidacion = ['index'];

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        $request = Yii::$app->request;

        if (in_array($action->id, $this->accionesSinValidacion)) {
            return parent::beforeAction($action);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$request->isAjax || !$request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->statusCode = 400;

            $errors = [];
            if (!$request->isAjax) {
                $errors['ajax'] = ['msg' => 'Debe realizarse una llamada AJAX'];
            }
            if (!$request->isPost) {
                $errors['post'] = ['msg' => 'Los valores deben ser enviados por POST'];
            }

            Yii::$app->response->data = [
                'respuesta' => Yii::$app->params['ERROR_CABECERA'] ?? 'Error en el envío de la cabecera',
                'errors' => $errors
            ];

            return false;
        }

        return parent::beforeAction($action);
    }

    protected function withTryCatch(callable $callback, string $dataKey = 'data'): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $callback();

            if (is_array($result) && array_key_exists('respuesta', $result)) {
                return $result;
            }

            Yii::$app->response->statusCode = 200;
            return [
                'respuesta' => Yii::$app->params['PROCESO_CORRECTO'] ?? 'ok',
                $dataKey => $result
            ];
        } catch (Exception $e) {
            Yii::error("Error en la base de datos: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'respuesta' => Yii::$app->params['ERROR_DB'] ?? 'Error en la base de datos',
                $dataKey => ''
            ];
        } catch (Throwable $e) {
            Yii::error("Error general: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'respuesta' => Yii::$app->params['ERROR_GENERAL'] ?? 'Error inesperado',
                $dataKey => ''
            ];
        }
    }
}
