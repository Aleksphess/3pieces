<?php

namespace frontend\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use common\components\SeoComponent;
use common\models\Pages;
use common\models\Lots;
use common\models\MainSlider;
use common\models\CatalogProducts;
use common\models\CatalogCategories;


class ContentController extends \common\components\BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup', 'static', 'contacts','login','reset','logup'],
                'rules' => [
                    [
                        'actions' => ['signup','logup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],

                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['static', 'contacts','login','reset'],
                        'allow' => true,
                        'roles' => ['@', '?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'index' => ['get'],
                ],
            ],
        ];
    }
    
    public function actionIndex()
    {

        $products=CatalogProducts::find()->joinWith('info','params')->joinWith('consist')->orderBy('sort ASC')->all();
        $categories=CatalogCategories::find()->active()->joinWith('info')->orderBy('sort DESC')->all();
        $slides=MainSlider::find()->joinWith('info')->orderBy('sort DESC')->all();
        return $this->render('index.twig', [
            'products'  =>  $products,
            'categories'    => $categories,
            'slides'        =>  $slides
        ]);
    }

    public function actionAbout()
    {
        SeoComponent::setByTemplate('default', [
            'name' => 'Контакты',
        ]);

        $page=Pages::find()->byAlias('about')->joinWith('info')->limit(1)->one();

        return $this->render('about.twig', [
            'page'  =>  $page

        ]);
    }


    public function actionStatic($alias)
    {

        $page=Pages::find()->byAlias($alias)->joinWith('info')->limit(1)->one();

        SeoComponent::setByTemplate('default', [
            'name' => $page->info->title,
        ]);

        return $this->render('static.twig', [
            'page' => $page,
        ]);
    }



    public function actionLogin()
    {
        return $this->render('signin.twig');
    }

    public function actionLogup()
    {


        return $this->render('logup.twig');
    }

}