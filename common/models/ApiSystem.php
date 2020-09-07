<?php

namespace common\models;

use yii\httpclient\Client;
use yii\helpers\Json;

class ApiSystem
{
    const INDICATIONS_HISTORY = 'indicationsHistory';
    const PAYMENT_HISTORY = 'paymentHistory';
    const MUTUAL_SETTLEMENTS = 'mutualSettlements';
    const METERES_INFO = 'meteresInfo';
    const METERE_INFO = 'metereInfo';
    const PAYMENT = 'payment';
    const PRINT_RECEIPT = 'printReceipt';
    const METERES = 'meteres';
    const FLUSH_METERES = 'flushMeteres';
    const PAYMENTS_COMMIT = 'paymentsCommit';

    protected $_params = [
        self::INDICATIONS_HISTORY => [
            'date'    => 'Data',
            'devices' => [
                'func' => 'getIndicationsHistoryDevicesRow',
                'args' => 'MeteringDevice',
            ],
        ],
        self::PAYMENT_HISTORY     => [
            'name'    => 'Name',
            'payment' => 'Payments',
        ],
        self::MUTUAL_SETTLEMENTS  => [
            'name'        => 'Name',
            'beginSaldo' => 'BeginSaldo',
            'endSaldo'   => 'EndSaldo',
            'charges'     => 'Charges',
            'payments'    => 'Payments',
        ],
        self::METERES_INFO        => [
            'name'             => 'Name',
            'indication'       => 'Indication',
            'id'               => 'ID',
            'verificationDate' => 'VerificationDate',
            'indicationDate'   => 'IndicationDate',
        ],
        self::METERE_INFO         => [
            'indication' => 'Indication',
            'date'       => 'Data',
            'expense'    => 'Expense',
        ],
        self::PAYMENT             => [
            'charges' => 'Charges',
            'saldo'   => 'Saldo',
            'sum'     => 'Summa',
            'id'      => 'ID',
            'name'    => 'Name',
        ],
        self::METERES             => [
            'name'          => 'Name',
            'indication'    => 'Indication',
            'id'            => 'ID',
            'date'          => 'IndicationDate',
            'maxIndication' => 'MaxIndication',
            'newIndication' => 'Indication',
        ],
    ];
    protected $allowed_methods = [
        self::INDICATIONS_HISTORY => '4580d01b-c411-11e9-a212-0cc47aff1845',
        self::PAYMENT_HISTORY     => '35f296fc-c411-11e9-a212-0cc47aff1845',
        self::PRINT_RECEIPT       => '6763dd44-c411-11e9-a212-0cc47aff1845',
        self::MUTUAL_SETTLEMENTS  => 'f6e5756c-c410-11e9-a212-0cc47aff1845',
        self::METERES_INFO        => '1ad057e7-c411-11e9-a212-0cc47aff1845',
        self::METERE_INFO         => '1ad057e7-c411-11e9-a212-0cc47aff1845',
        self::PAYMENT             => '997c0f3d-c411-11e9-a212-0cc47aff1845',
        self::METERES             => '1ad057e7-c411-11e9-a212-0cc47aff1845',
        self::FLUSH_METERES       => '57337b9b-c411-11e9-a212-0cc47aff1845',
        self::PAYMENTS_COMMIT     => '7a47c1b6-c411-11e9-a212-0cc47aff1845',
    ];

    protected $url = 'http://WebUser:Te4kutol@95.80.77.140:9020/Abo1C_Gradsoft/hs/AboExchange/StoredProc';
    protected $account_number;
    protected $client;

    public function __construct($account_number)
    {
        $this->account_number = $account_number;
        $this->client         = new Client([
            'baseUrl'        => $this->url,
            'requestConfig'  => [
                'format' => Client::FORMAT_JSON,
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON,
            ],
        ]);
    }

    public function call($method, $params = [])
    {
        if (!array_key_exists($method, $this->allowed_methods)) {
            return ['err' => 'Произошла ошибка. Обновите страницу'];
        }

        if ($params) {
            $params = $this->prepareParams($params);
        }

        $params['GUID']    = $this->allowed_methods[$method];
        $params['Account'] = $this->account_number;

        $response = $this->client->createRequest()
            ->addHeaders([
                'content-type'   => 'application/json',
                'content-length' => mb_strlen(Json::encode($params)),
            ])
            ->setMethod('POST')
            ->setData($params)
            ->send();

        if (!$response->isOk) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        return $this->$method($response->data);
    }

    protected function prepareParams($params)
    {
        foreach ($params as $key => $val) {
            if (!is_array($val) && !trim($val)) {
                unset($params[$key]);
                continue;
            }
            switch ($key) {
                case 'dateFrom':
                    $new_key = 'BeginDate';
                    $val     = date('Ymd', strtotime($val));
                    break;
                case 'dateTo':
                    $new_key = 'EndDate';
                    $val     = date('Ymd', strtotime($val));
                    break;
                case 'deviceId':
                    $new_key = 'MeteringDeviceID';
                    break;
                case 'meteres':
                    $new_key = 'MeteringDevice';
                    $tmp_val = [];
                    foreach ($val as $device_id => $indication) {
                        $tmp_val[] = [
                            'MeteringDeviceID' => $device_id,
                            'Indication'       => $indication,
                        ];
                    }
                    $val = $tmp_val;
                    break;
                case 'paymentId':
                    $new_key = 'Number';
                    break;
                case 'createDate':
                    $new_key = 'Date';
                    $val     = date('Ymd', strtotime($val));
                    break;
                case 'transactionId':
                    $new_key = 'TransactionID';
                    break;
                case 'payments':
                    $new_key = 'PaymentTable';
                    $tmp_val = [];
                    foreach ($val['devices'] as $device) {
                        $tmp_val[] = [
                            'ServiceID'        => $device['serviceId'],
                            'MeteringDeviceID' => $device['deviceId'],
                            'Summa'            => $device['value'],
                        ];
                    }
                    $val = $tmp_val;
                    break;
                default:
                    $new_key = $key;
            }
            $new_val = $val;
            unset($params[$key]);
            $params[$new_key] = $new_val;
        }

        return $params;
    }

    protected function indicationsHistory($data)
    {
        if (empty($data['MeteringDevice']) || empty($data['IndicationTable'])) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        $devices = [
            [
                'text'  => '',
                'value' => 'data-table-expand',
            ],
            [
                'text'  => 'Период',
                'value' => 'date',
            ],
        ];
        foreach ($data['MeteringDevice'] as $device) {
            $devices[] = [
                'text'  => $device['Name'],
                'value' => $device['ID'],
            ];
        }

        return [
            'headers' => $devices,
            'items'   => $this->getArrayRecursive($data['IndicationTable'], $this->_params[self::INDICATIONS_HISTORY]),
        ];
    }

    protected function getIndicationsHistoryDevicesRow($devices)
    {
        $result = [];
        foreach ($devices as $device) {
            $result[$device['ID']] = $device['Indication'];
        }

        return $result;
    }

    protected function paymentHistory($data)
    {
        if (empty($data['PaymentTable'])) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        return [
            'headers' => [
                [
                    'text'  => '',
                    'value' => 'data-table-expand',
                ],
                [
                    'text'  => 'Период',
                    'value' => 'name',
                ],
                [
                    'text'  => 'Оплачено',
                    'value' => 'payment',
                ],
            ],
            'items'   => $this->getArrayRecursive($data['PaymentTable'], $this->_params[self::PAYMENT_HISTORY]),
        ];
    }

    protected function printReceipt($data)
    {
        $file_name = md5($data['SlipFile']) . '.pdf';
        $file_path = "{$_SERVER['DOCUMENT_ROOT']}/upload/files/receipts/{$file_name}";
//        $data      = base64_decode($data['SlipFile']);
        if (!$data['SlipFile']) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        return ['receipt' => $data['SlipFile']];

//        if (file_put_contents($file_path, $data) === false) {
//            return ['err' => 'Произошла ошбика. Попробуйте еще раз.'];
//        }
//
//        return ['link' => sprintf(
//            "%s://%s%s",
//            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
//            $_SERVER['SERVER_NAME'],
//            "/upload/files/receipts/$file_name"
//        )];
    }

    protected function mutualSettlements($data)
    {
        if (empty($data['CalculationTable'])) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        return [
            'headers' => [
                [
                    'text'  => '',
                    'value' => 'data-table-expand',
                ],
                [
                    'text'  => 'Период',
                    'value' => 'name',
                ],
                [
                    'text'  => 'Начальное сальдо',
                    'value' => 'beginSaldo',
                ],
                [
                    'text'  => 'Начислено',
                    'value' => 'charges',
                ],
                [
                    'text'  => 'Оплачено',
                    'value' => 'payments',
                ],
                [
                    'text'  => 'Конечное сальдо',
                    'value' => 'endSaldo',
                ],
            ],
            'items'   => $this->getArrayRecursive($data['CalculationTable'], $this->_params[self::MUTUAL_SETTLEMENTS]),
        ];
    }

    protected function meteresInfo($data)
    {
        if (empty($data['MeteringDevice'])) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        return [
            'headers' => [
                [
                    'text'  => '',
                    'value' => 'data-table-expand',
                ],
                [
                    'text'  => 'Прибор учета',
                    'value' => 'name',
                ],
                [
                    'text'  => 'Дата следующей поверки',
                    'value' => 'verificationDate',
                ],
                [
                    'text'  => 'Дата последней передачи показаний',
                    'value' => 'indicationDate',
                ],
                [
                    'text'  => 'Последние переданные показания',
                    'value' => 'indication',
                ],
            ],
            'items'   => $this->getArrayRecursive($data['MeteringDevice'], $this->_params[self::METERES_INFO]),
        ];
    }

    protected function metereInfo($data)
    {
        if (empty($data['IndicationTable'])) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        return [
            'headers' => [
                [
                    'text'  => '',
                    'value' => 'data-table-expand',
                ],
                [
                    'text'  => 'Период',
                    'value' => 'date',
                ],
                [
                    'text'  => 'Переданные показания',
                    'value' => 'indication',
                ],
                [
                    'text'  => 'Расход',
                    'value' => 'expense',
                ],
            ],
            'items'   => $this->getArrayRecursive($data['IndicationTable'], $this->_params[self::METERE_INFO]),
        ];
    }

    protected function payment($data)
    {
        if (empty($data['ChargesTable'])) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        return [
            'headers' => [
                [
                    'text'  => '',
                    'value' => 'data-table-expand',
                ],
                [
                    'text'  => 'Услуга/Прибор учета',
                    'value' => 'name',
                ],
                [
                    'text'  => 'Начислено',
                    'value' => 'charges',
                ],
                [
                    'text'  => 'Долг/Переплата',
                    'value' => 'saldo',
                ],
                [
                    'text'  => 'К оплате',
                    'value' => 'sum',
                ],
            ],
            'items' => $this->getArrayRecursive($data['ChargesTable'], $this->_params[self::PAYMENT]),
        ];
    }

    protected function meteres($data)
    {
        if (empty($data['MeteringDevice'])) {
            return ['err' => 'Произошла ошибка или данных нет в системе.'];
        }

        return [
            'headers' => [
                [
                    'text'  => '',
                    'value' => 'data-table-expand',
                ],
                [
                    'text'  => 'Прибор учета',
                    'value' => 'name',
                ],
                [
                    'text'  => 'Дата последней передачи показаний',
                    'value' => 'date',
                ],
                [
                    'text'  => 'Последние переданные показания',
                    'value' => 'indication',
                ],
                [
                    'text'  => 'Текущие показания',
                    'value' => 'newIndication',
                    'width' => '25%',
                ],
            ],
            'items'   => $this->getArrayRecursive($data['MeteringDevice'], $this->_params[self::METERES]),
        ];
    }

    protected function flushMeteres($data)
    {
        if (empty($data['Result'])) {
            return ['err' => 'Что-то пошло не так. Попробуйте позднее.'];
        }

        return ['message' => 'Показания переданы.'];
    }

    protected function paymentsCommit($data)
    {
        if (empty($data['Result'])) {
            return ['err' => 'Оплата будет учтена в ближайшие сутки.'];
        }

        return ['err' => false];
    }

    protected function getArrayRecursive($data, $params)
    {
        $result = [];
        $row_id = 0;

        foreach ($data as $row) {
            $tmp_row = [];
            foreach ($params as $param_key => $row_key) {
                if (is_array($row_key)) {
                    $tmp_row = array_merge($tmp_row, call_user_func([$this, $row_key['func']], $row[$row_key['args']]));
                }
                else {
                    $tmp_row[$param_key] = $row[$row_key];
                }
//                $tmp_row[$param_key] = is_array($row_key)
//                    ?
//                    call_user_func([$this, $row_key['func']], $row[$row_key['args']])
//                    :
//                    $row[$row_key];
            }
            $tmp_row['idRow']    = $row_id++;
            $tmp_row['children'] = empty($row['DetailRow']) ? null : $this->getArrayRecursive($row['DetailRow'], $params);
            $result[]            = $tmp_row;
//            if (!empty($result['DetailRow'])) {
//                $result = array_merge($result, $this->getArrayRecursive($row['DetailRow'], $params));
//            }
        }

        return $result;
    }
}