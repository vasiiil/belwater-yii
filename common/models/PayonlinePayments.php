<?php


namespace common\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class PayonlinePayments model
 * @package common\models
 *
 * @property integer id
 * @property integer paymentId
 * @property string accountNumber
 * @property string paymentParams
 * @property integer createDate
 * @property integer updateDate
 * @property integer paymentDate
 * @property integer transactionId
 * @property float amount
 * @property string currency
 * @property string cardHolder
 * @property string cardNumber
 * @property integer errorCode
 * @property integer code
 * @property integer paySuccess
 * @property integer commitSuccess
 */
class PayonlinePayments extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tblPayonlinePayments';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'      => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createDate', 'updateDate'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updateDate'],
                ],
                // если вместо метки времени UNIX используется datetime:
                'value'      => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['paymentId', 'trim'],
            ['paymentId', 'required'],

            ['paymentParams', 'trim'],
            ['paymentParams', 'required'],

            ['accountNumber', 'trim'],
            ['accountNumber', 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findByPaymentId($id)
    {
        return static::findOne(['paymentId' => $id]);
    }

    public static function getPaymentUrl($paymentId, $Amount)
    {
        //Указываем локализацию (доступно ru | en | fr)
        $Language = "ru";
        // Указываем идентификатор мерчанта
        $MerchantId = '80814';
        //Указываем приватный ключ (см. в ЛК PayOnline в разделе Сайты -> настройка -> Параметры интеграции)
        $PrivateSecurityKey = '12f7c5ae-c54f-4493-9793-39643c5b29e3';
        //Валюта (доступны следующие валюты | USD, EUR, RUB)
        $Currency = 'RUB';
        //Описание заказа (не более 100 символов, запрещено использовать: адреса сайтов, email-ов и др.) необязательный параметр
        $OrderDescription = "Оплата коммунальных услуг. Сумма {$Amount} руб.";
        //$OrderDescription = "Payments. Sum {$Amount} RUB.";
        //Срок действия платежа (По UTC+0) необязательный параметр
        ////$ValidUntil="2013-10-10 12:45:00";
        /// //В случае неуспешной оплаты, плательщик будет переадресован, на данную страницу.
        $FailUrl = "http://207.154.206.95/personal/paymentResult/{$paymentId}";
        // В случае успешной оплаты, плательщик будет переадресован, на данную страницу.
        $ReturnUrl = "http://207.154.206.95/personal/paymentResult/{$paymentId}/ok";

        $params = 'MerchantId=' . $MerchantId;
        $params .= '&OrderId=' . $paymentId;
        $params .= '&Amount=' . $Amount;
        $params .= '&Currency=' . $Currency;
        if (strlen($OrderDescription) < 101 && strlen($OrderDescription) > 1) {
            $params .= '&OrderDescription=' . $OrderDescription;
        }
        $params .= '&PrivateSecurityKey=' . $PrivateSecurityKey;
        $SecurityKey = md5($params);
        $Paymenturl  = "https://secure.payonlinesystem.com/" . $Language . "/payment/";
        $url_query   = "?MerchantId=" . $MerchantId . "&OrderId=" . urlencode($paymentId) . "&Amount=" . $Amount . "&Currency=" . $Currency;
        
        if ($OrderDescription) {
            $url_query .= "&OrderDescription=" . urlencode($OrderDescription);
        }
        
        if ($ReturnUrl) {
            $url_query .= "&ReturnUrl=" . urlencode($ReturnUrl);
        }
        
        if ($FailUrl) {
            $url_query .= "&FailUrl=" . urlencode($FailUrl);
        }
        
        $url_query .= "&SecurityKey=" . $SecurityKey;
        $url_full  = $Paymenturl . $url_query;

        if ($url_full) {
            return ['link' => $url_full];
        }

        return ['err' => 'Ошибка платежной системы. Повторите попытку позже.'];
    }
}