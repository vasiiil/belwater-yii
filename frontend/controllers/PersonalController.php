<?php

namespace frontend\controllers;

use common\models\ApiSystem;
use common\models\PayonlinePayments;
use common\models\User;
use Yii;
use yii\helpers\Json;
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
    
    public function actionPay()
    {
        if (!$this->client) {
            return ['err' => 'Ошибка. Обновите страницу и попробуйте еще раз.'];
        }
        
        $model = new PayonlinePayments();
        $paymentId = (int)microtime(true);
        $paymentParams = Yii::$app->request->post('params');
        $paymentParams['sum'] = number_format($paymentParams['sum'], 2, '.', '');
        
        $model->paymentId = $paymentId;
        $model->paymentParams = Json::encode($paymentParams);
        $model->accountNumber = $this->user->accountNumber;

        if (
            $model->validate() 
            && $model->save()
        ) {
            return PayonlinePayments::getPaymentUrl($paymentId, $paymentParams['sum']);
        }

        return ['err' => 'Ошибка. Обновите страницу и попробуйте еще раз.'];
    }
    
    public function actionGetPayment()
    {
        $paymentId = Yii::$app->request->post('params');

        if (!$paymentId) {
            return ['err' => 'Данные об оплате не найдены'];
        }
        
        $payment = PayonlinePayments::findByPaymentId($paymentId);
        if (!$payment) {
            return ['err' => 'Данные об оплате не найдены'];
        }
        
        $params = Json::decode($payment->paymentParams);
        $idRow = 0;
        foreach ($params['devices'] as &$device) {
            $device['idRow'] = $idRow++;
        }
        unset($device);
        return [
            'headers' => [
                [
                    'text'  => '',
                    'value' => 'data-table-expand',
                ],
                [
                    'text'  => 'Услуга',
                    'value' => 'serviceName',
                ],
                [
                    'text'  => 'ПУ',
                    'value' => 'deviceName',
                ],
                [
                    'text'  => 'Сумма',
                    'value' => 'value',
                ],
            ],
            'items' => $params['devices'],
            'sum' => $params['sum']
        ];
    }
}
