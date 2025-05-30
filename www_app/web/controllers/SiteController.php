<?php

namespace app\web\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use app\assets\AppAsset;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public $layout = 'main.twig';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $view = Yii::$app->view;
        $view->params['powered_yii'] = Html::a('Yii Framework', 'https://www.yiiframework.com/', ['rel' => 'external']);
        $view->params['powered_twig'] = Html::a('Twig', 'https://twig.symfony.com/', ['rel' => 'external']);
        // $view->params['meta_description'] = 'description';
        // $view->params['meta_keywords'] = 'keywords';
         return parent::beforeAction($action);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->view->title = Yii::t('app', 'My Yii Application-test');
        return $this->render('index.twig');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login.twig', [
            'title' => 'Login',
            'breadcrumbs' => ['Login'],
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact.twig', [
            'title' => 'Contact',
            'breadcrumbs' => ['Contact'],
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about.twig', [
            'title' => 'About',
            'breadcrumbs' => ['About'],
        ]);
    }

    /**
     * Displays Vue.js application.
     *
     * @return string
     */
    public function actionVue(): string
    {
        return $this->renderFile(Yii::getAlias('@webroot/html/index.html'));
    }
}
