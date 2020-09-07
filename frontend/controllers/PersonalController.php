<?php

namespace frontend\controllers;

use common\models\ApiSystem;
use common\models\User;
use Yii;
use yii\web\Controller;

class PersonalController extends Controller
{
    protected $user;
    protected $client;
    protected $method;
    protected $params;
    
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->user = User::findIdentityByAccessToken(Yii::$app->request->post('token'));
        $this->method = Yii::$app->request->post('method');
        $this->params = Yii::$app->request->post('params');
        if ($this->user) {
            $this->client = new ApiSystem($this->user->accountNumber) ;
        }
    }
    
    public function actionGetData()
    {
        if (!$this->client) {
            return ['err' => 'Произошла ошибка. Обновите страницу.'];
        }

        return $this->client->call($this->method, $this->params);
    }
}
