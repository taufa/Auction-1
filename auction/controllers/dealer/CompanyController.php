<?php

namespace auction\controllers\dealer;

use auction\components\Auction;
use auction\components\controllers\DealerController;
use auction\components\helpers\DatabaseHelper;
use auction\models\DealerCompany;
use auction\models\DealerCompanyPreferences;
use Yii;
use auction\models\Companies;
use auction\models\forms\SearchCompany;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CompaniesController implements the CRUD actions for Companies model.
 */
class CompanyController extends DealerController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Companies models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SearchCompany();
        $dataProvider = $searchModel->searchDealerCompany();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /*
     * Displays a single Companies model.
     * @param integer $id
     * @return mixed
     * @throw http 404 when no company found
     */
    public function actionView($id)
    {
        $model = Companies::find()->with([
            'dealerCompanies' => function ($query) use ($id){
                $query->joinWith([
                    'dealerCompanyPreferences' => function ($query) {
                        $query->joinWith([
                            'brand0' => function($query){
                                $query->select('name');
                            },
                            'category0' => function($query){
                                $query->select('name');
                            }
                        ])->asArray();
                    }
                ])->where([
                    'dealer_company.company' => $id,
                    'dealer_company.dealer' => 3
                ]);
            }
        ])->where([
            'id' => $id
        ])->one();

        if($model === null){
            Auction::error('There is no Company with Company Id '.$id);
            throw new HttpException(404, 'No Company Found');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Companies model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Companies();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Companies model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Companies model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete()
    {
        $id= Auction::$app->request->post('id',0);

        if($id){
            $model = $this->findModel($id);
            $model->is_active = DatabaseHelper::IN_ACTIVE;

            if(!$model->save()){
                return false;
            }
        }
    }

    /**
     * Finds the Companies model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Companies the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DealerCompany::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionEditPreferences(){
        $id= Auction::$app->request->post('id',0);
        if($id){

            $model = DealerCompanyPreferences::findAll('dc_id=:id',[':id' => $id]);

            if(!$model){
                $model = new DealerCompanyPreferences();
            }

            return $this->renderPartial('_form',['model' => $model]);

        }
    }

    public function actionListCompanies($term){

        $array = Companies::find()->select('id,name,logo_image as image')
                ->where(['like' , 'name' , $term])
                ->asArray()->all();

        return Json::encode($array);
    }
}