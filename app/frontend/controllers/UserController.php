<?php

namespace frontend\controllers;

use Twig\Node\Expression\Binary\AddBinary;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\SeoComponent;
use common\models\Lots;
use common\models\User;
use yii\imagine\Image;
use common\models\Payment;
use common\models\AddressDelivery;

class UserController extends \common\components\BaseController
{
    
    public function behaviors()
    {
        return [
            'access' => [
                'class'     => AccessControl::className(),
//                'only'      => ['index'],
                'rules'     => [
                    [
                        'actions'   => ['index', 'settings', 'change-settings','payment-static','lot','change-password','add-address','delete-address','change-address'],
                        'allow'     => true,
                        'roles'     => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index'                 => ['get'],
                    'settings'              => ['get'],
                    'change-settings'       => ['post'],
                    'change-password'       => ['post'],
                    'add-address'           => ['post'],
                    'delete-address'        => ['post'],
                    'change-address'        => ['post']
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
    
    public function actionIndex()
    {
                return $this->render('old_index.twig', [
										   ]);
    }
    

    
    public function actionChangeSettings()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

            $current_user = User::findIdentity(Yii::$app->user->identity->id);
            $current_user->username=trim(strip_tags($post['username']));
            $current_user->email=trim(strip_tags($post['email']));
            $current_user->phone=trim(strip_tags($post['phone']));
            $current_user->facebook=trim(strip_tags($post['facebook']));
            $current_user->vk=trim(strip_tags($post['vk']));

            if( $current_user->update())
            {
                         return 'success';
            }
            return ['status' => false];


    }
    public function actionChangePassword()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        $current_user = User::findIdentity(Yii::$app->user->identity->id);
        $current_user->setPassword($post['password']);

        if( $current_user->update())
        {
            return 'success';
        }
        return ['status' => false];


    }
    public function actionAddAddress()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $address = new AddressDelivery();
        $address->user_id = Yii::$app->user->identity->id;
        $address->address = $post['address'];
        $this->layout='main_added.twig';
        if( $address->save())
        {


            return $this->renderAjax('added.twig',['address' => $address]);
        }
        return ['status' => false];


    }
    public function actionChangeAddress()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $address =  AddressDelivery::find()->where(['id'=>$post['id']])->limit(1)->one();

        $address->address = $post['address'];

        if( $address->update())
        {
            return 'success';
        }
        return ['status' => false];


    }
    public function actionDeleteAddress()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $address = AddressDelivery::find()->where(['id'=>$post['id']])->limit(1)->one();

        if( $address->delete())
        {

            return 'success';
        }
        return ['status' => false];


    }

    public function actionLot($alias)
    {

        $lot = Lots::find()->byAlias($alias, Lots::tableName())->byUser()
            ->where([Lots::tableName().'.status_id' => Lots::IN_PUBLIC])
            ->where([Lots::tableName().'.alias' => $alias])
            ->limit(1)->one();
        if($lot->owner_id!=Yii::$app->user->identity->id)
        {
            throw new NotFoundHttpException('Not Found!', 404);

        }
        return $this->render('lot.twig',[
            'lot'   =>  $lot,
        ]);
    }
    public function actionLots()
    {

        $lots = Lots::find()->byUser()
            ->all();
        return $this->render('lots.twig',[
            'lots'   =>  $lots,
        ]);
    }
}