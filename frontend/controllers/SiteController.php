<?php
namespace frontend\controllers;

use common\models\Estado;
use common\models\seguridad\Modulo;
use common\models\seguridad\Usuario;
use common\models\seguridad\UsuarioContextoActivo;
use yii\captcha\CaptchaAction;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ErrorAction;
use yii\web\Controller;
use yii\web\Response;
use Yii;

/**
 * Site controller
 * @noinspection PhpUnused
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['portal-login', 'usuario-invalido', 'error'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'log-out', 'about', 'seleccionar-modulo'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'log-out' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @param string|null $cu
     *
     *  Logs in a user.
     *
     * @return Response
     * @noinspection PhpUnused
     */
    public function actionPortalLogin(string $cu = null ): Response
    {
        if (!$cu) {
            return $this->redirect(['site/usuario-invalido']);
        }

        $usuario = Usuario::find()
            ->where(['TokenPortal' => $cu])
            ->one();

        if (!$usuario) {
            return $this->redirect(['site/usuario-invalido']);
        }

        /** @noinspection PhpParamsInspection */
        Yii::$app->user->login($usuario, 1800);

        Yii::$app->session->regenerateID(true);

        return $this->redirect(['site/index']);
    }

    /**
     * display unauthorizedpage
     *
     * @return string
     * @noinspection PhpUnused
    */
    public function actionUsuarioInvalido(): string
    {
        $this->layout = 'public';

        return $this->render('usuario-invalido');
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $usuario = Yii::$app->user->identity;

        $modulos = Modulo::find()
            ->alias('m')
            ->innerJoin(
                'seguridad.UsuarioModulo um',
                'um.IdModulo = m.IdModulo'
            )
            ->where(['um.IdUsuario' => $usuario['IdUsuario']])
            ->andWhere(['m.Visible' => true])
            ->andWhere(['m.CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy('m.Orden')
            ->all();

        return $this->render('index', [
            'modulos' => $modulos
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionSeleccionarModulo($id): Response
    {
        $contexto = UsuarioContextoActivo::find()
            ->where([
                'IdUsuario' => Yii::$app->user->id
            ])
            ->one();

        if (!$contexto) {

            $contexto = new UsuarioContextoActivo();

            $contexto->IdUsuario = Yii::$app->user->id;
        }

        $contexto->IdModulo = $id;
        $contexto->CodigoEstado = Estado::ESTADO_VIGENTE;
        $contexto->FechaHoraActualizacion = date('Y-m-d H:i:s');
        $contexto->Usuario=Yii::$app->user->identity->IdUsuario;


        if (!$contexto->save()) {

            echo '<pre>';

            print_r($contexto->errors);

            die();
        }

        $modulo = Modulo::findOne($id);

        return $this->redirect([
            $modulo->DashboardRoute
        ]);
    }


    /**
     * Logs out the current user.
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function actionLogout(): string
    {
        Yii::$app->user->logout();
        $this->layout = 'public';
        return $this->render('logout');
    }

    /**
     * Displays about page.
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }

}
