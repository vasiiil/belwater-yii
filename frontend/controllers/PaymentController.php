<?php


namespace frontend\controllers;


use common\models\ApiSystem;
use common\models\PayonlinePayments;
use yii\helpers\Json;
use yii\web\Controller;

class PaymentController extends Controller
{
    public function actionCommit()
    {
        $get = \Yii::$app->request->get();
        $paymentId = $get['OrderId'];
        
        $payment = PayonlinePayments::findByPaymentId($paymentId);
        if ($payment) {
            $save = true;
            $payment->paymentDate = date('Y-m-d H:i:s', strtotime("{$get['DateTime']} + 3hours"));
            $payment->transactionId = $get['TransactionID'];
            $payment->amount = $get['Amount'];
            $payment->currency = $get['Currency'];
            $payment->cardHolder = $get['CardHolder'];
            $payment->cardNumber = $get['CardNumber'];
            $payment->errorCode = $get['ErrorCode'] ?? null;
            $payment->code = $get['Code'] ?? null;
            $payment->paySuccess = (int) !$payment->errorCode;
            
            if (!$payment->save()) {
                if (!$payment->save()) {
                    if (!$payment->save()) {
                        $save = false;
                    }
                }
            }
            
            if ($save && $payment->paySuccess) {
                $api = new ApiSystem($payment->accountNumber);
                $commitResult = true;
                $commit = $api->call('paymentsCommit', [
                    'paymentId' => $paymentId,
                    'createDate' => $payment->createDate,
                    'transactionId' => $payment->transactionId,
                    'payments' => Json::decode($payment->paymentParams, true),
                ]);
                
                if ($commit['err']) {
                    $commit = $api->call('paymentsCommit', [
                        'paymentId' => $paymentId,
                        'createDate' => $payment->createDate,
                        'transactionId' => $payment->transactionId,
                        'payments' => Json::decode($payment->paymentParams, true),
                    ]);
                    
                    if ($commit['err']) {
                        $commit = $api->call('paymentsCommit', [
                            'paymentId' => $paymentId,
                            'createDate' => $payment->createDate,
                            'transactionId' => $payment->transactionId,
                            'payments' => Json::decode($payment->paymentParams, true),
                        ]);
                        
                        if ($commit['err']) {
                            $commitResult = false;
                        }
                    }
                }
                
                if ($commitResult) {
                    $payment->commitSuccess = 1;
                    if (!$payment->save()) {
                        if (!$payment->save()) {
                            $payment->save();
                        }
                    }
                }
            }
        }
        
        return [];
    }
}