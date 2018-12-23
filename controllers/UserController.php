<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use Symfony\Component\Finder\Exception\AccessDeniedException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['transfer', 'transfer-done'],
                'rules' => [
                    // allow authenticated users
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Transfer balance to target user.
     * @return mixed
     */
    public function actionTransfer()
    {
        $model = $this->findModel(\Yii::$app->user->id);
        if (! $model) {
            throw new AccessDeniedException();
        }

        $model->scenario = User::SCENARIO_TRANSFER;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('transfer-done', true);
            return $this->redirect(['user/transfer-done']);
        }

        return $this->render('transfer', [
            'model' => $model
        ]);
    }

    /**
     * Render successful transfer.
     * @return mixed
     */
    public function actionTransferDone()
    {
        $model = $this->findModel(\Yii::$app->user->id);
        if (! $model) {
            throw new AccessDeniedException();
        }

        return $this->render('transfer_done', [
            'model' => $model
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
