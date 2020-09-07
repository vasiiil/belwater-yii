<?php


namespace backend\controllers;


use backend\models\User;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;

class UsersController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow'   => true,
                    ],
                    [
                        'actions' => ['index', 'delete'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
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
        ];
    }

    public function actionIndex()
    {
        return $this->renderIndex();
    }

    public function actionDelete($id)
    {
        if (\Yii::$app->getRequest()->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if ($user = User::findById($id)) {
                if ($user->id === \Yii::$app->user->id) {
                    return ['err' => 'Нельзя удалить самого себя'];
                }
                $user->del = 1 - $user->del;
                if ($user->save()) {
                    return ['success' => true];
                }
                return ['err' => 'Не удалось удалить'];
            }
            return ['err' => 'Пользователь не найден'];
        }
        
        
        return $this->redirect(['index']);
    }
    
    private function renderIndex()
    {
        $query    = User::find();
        $show_all = \Yii::$app->request->get('show_all');

        if (!$show_all) {
            $query = $query->where(['del' => 0]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 2,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'show_all'     => (bool)$show_all,
        ]);
    }
}