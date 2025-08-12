<?php
namespace app\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\exceptions\BusinessException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use yii\db\Exception as DbException;
use yii\web\BadRequestHttpException;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;
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
        if (in_array($action->id, $this->accionesSinValidacion)) {
            return parent::beforeAction($action);
        }

        $request = Yii::$app->request;
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

            Yii::$app->response->data = ResponseHelper::error(Yii::$app->params['ERROR_CABECERA'],$errors);

            return false;
        }

        return parent::beforeAction($action);
    }

    protected function withTryCatch(callable $callback): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            Yii::$app->response->statusCode = 201;
            $result = $callback();

            if (is_array($result) && array_key_exists('success', $result)) {
                return $result;
            }

            return ResponseHelper::success($result['data'], $result['message']);

        } catch (BusinessException | ValidationException $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = $e->getCode();
            return ResponseHelper::error($e->getMessage(),['errores' => $e->getErrors()]);

        } catch (DbException  $e) {
            Yii::error("Error en la base de datos: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return ResponseHelper::error(Yii::$app->params['ERROR_DB'],['error'=>$e->getMessage()]);

        } catch (Exception $e) {
            Yii::error("Error general: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return ResponseHelper::error(Yii::$app->params['ERROR_GENERAL'],['error'=>$e->getMessage()]);

        } catch (Throwable $e) {
            Yii::error("Error inesperado: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return ResponseHelper::error(Yii::$app->params['ERROR_GENERAL'],['error'=>$e->getMessage()]);
        }
    }
}
