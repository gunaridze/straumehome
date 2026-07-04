<?php
namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\SystemException;
use Bitrix\Sale\OrderBase;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Logger;
use Bitrix\Sale\PaySystem\ServiceResult;
use Bitrix\Sale\PaySystem\Service;
use Bitrix\Sale\PriceMaths;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\Context;
use Bitrix\Main\Diag\Debug;

Loc::loadMessages(__FILE__);

class SbpSberHandler extends PaySystem\ServiceHandler implements PaySystem\IHold
{
	const API_BASE_URL = 'https://mc.api.sberbank.ru:443/prod/';//'https://api.sberbank.ru:8443/prod/';
    const API_AUTH_URL = 'tokens/v3/oauth';
    const API_CREATE_URL = 'qr/order/v3/creation';
    const API_STATUS_URL = 'qr/order/v3/status';
    const API_REVOKE_URL = 'qr/order/v3/revocation';
    const API_CANCEL_URL = 'qr/order/v3/cancel';

    const SCOPE_CREATE = 'https://api.sberbank.ru/qr/order.create';
    const SCOPE_STATUS = 'https://api.sberbank.ru/qr/order.status';
    const SCOPE_REVOKE = 'https://api.sberbank.ru/qr/order.revoke';
    const SCOPE_CANCEL = 'https://api.sberbank.ru/qr/order.cancel';

    const STATUS_PAID = 'PAID';
    const STATUS_CREATED = 'CREATED';
    const STATUS_REVERSED = 'REVERSED';
    const STATUS_REFUNDED = 'REFUNDED';
    const STATUS_REVOKED = 'REVOKED';
    const STATUS_DECLINED = 'DECLINED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_AUTHORIZED = 'AUTHORIZED';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_ON_PAYMENT = 'ON_PAYMENT';

    const SPB_MEMBER_ID = '100000000111';

    /**
     * @param Payment $payment
     * @param Request|null $request
     * @return ServiceResult
     */
    public function initiatePay(Payment $payment, Request $request = null): ServiceResult
    {
        $result = new ServiceResult();

        $createResult = $this->create($payment);
        if (!$createResult->isSuccess()){
            $result->addErrors($createResult->getErrors());
            return $result;
        }

        $createData = $createResult->getData();
        $result->setPaymentUrl($createData['order_form_url']);

        $payment->setField('PS_INVOICE_ID', $createData['order_id']);

        $saveResult = $payment->save();
        if(!($saveResult->isSuccess())){
            $result->addErrors($saveResult->getErrors());
            return $result;
        }

        if(!$result->getPaymentUrl()){
            $result->addError(new PaySystem\Error(Loc::getMessage('SALE_HPS_SBP_SBER_ERROR_PAYMENT')));
            return $result;
        }

        $this->setExtraParams(
            [
                'PAYMENT_URL' => $result->getPaymentUrl(),
                'PAYMENT_ID' => $payment->getId(),
                'PAY_SYSTEM_ID' => $payment->getPaymentSystemId()
            ]
        );

        $showTemplateResult = $this->showTemplate($payment, $this->getTemplateName());
        if ($showTemplateResult->isSuccess()){
            $result->setTemplate($showTemplateResult->getTemplate());
        } else{
            $result->addErrors($showTemplateResult->getErrors());
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getTemplateName(): string
    {
        return 'template';
    }

    /**
     * @return string[]
     */
    public function getCurrencyList()
    {
        return ['RUB'];
    }

    public function processRequest(Payment $payment, Request $request): ServiceResult
    {
        $result = new ServiceResult();

        $statusResult = $this->getStatus($payment);
        if ($statusResult->isSuccess()){

            $statusData = $statusResult->getData();

            $description = Loc::getMessage('SALE_HPS_SBP_SBER_TRANSACTION', [
                '#ID#' => $statusData['order_id'],
            ]);

            $fields = [
                'PS_INVOICE_ID' => $statusData['order_id'],
                'PS_STATUS_CODE' => $statusData['order_state'],
                'PS_STATUS_DESCRIPTION' => $description,
                'PS_SUM' => $payment->getSum(),
                'PS_STATUS' => 'N',
                'PS_CURRENCY' => $payment->getOrder()->getCurrency(),
                'PS_RESPONSE_DATE' => new \Bitrix\Main\Type\DateTime()
            ];

            if ($statusData['order_state'] === static::STATUS_PAID){

                $fields['PS_STATUS'] = 'Y';

                PaySystem\Logger::addDebugInfo(
                    __CLASS__.': PS_CHANGE_STATUS_PAY='.$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
                );

                if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y'){
                    $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
                }

                $result->setData(['PAID' => true]);

            }

            $result->setPsData($fields);

        } else {
            $result->addErrors($statusResult->getErrors());
        }

        return $result;
    }

    public function sendResponse(ServiceResult $result, Request $request)
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();

        $resultData = $result->getData();

        echo Json::encode(
            [
                'paid' => (bool) $resultData['PAID']
            ]
        );
        die();
    }

    public function getPaymentIdFromRequest(Request $request): int
    {
        return (int) $request->get('paymentId');
    }

    public static function isMyResponse(Request $request, $paySystemId): bool
    {
        return (int) $request->get('paySystemId') === (int) $paySystemId;
    }

    protected static function getRquid(int $length = 32): string
    {
        $permitted_chars = 'abcdefABCDEF0123456789';
        $input_length = strlen($permitted_chars);
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }

    protected function getHeaderAuthorization(Payment $payment): string
    {
        return 'Basic '
            .base64_encode($this->getBusinessValue($payment, 'CLIENT_ID')
            .':'
            .$this->getBusinessValue($payment, 'CLIENT_SECRET'))
        ;
    }

    protected function send(
        Payment $payment,
        string $operation,
        array $headers,
        array $body,
        bool $isJson = true
    ): ServiceResult
    {
        $result = new ServiceResult();

        $curl = curl_init();

        $curlOptions = [
            CURLOPT_URL => static::API_BASE_URL . $operation,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ($isJson) ? Json::encode($body) : http_build_query($body),
            CURLOPT_HTTPHEADER => $headers,
//			CURLOPT_CAINFO => '/home/bitrix/cert/sbp_sber_qr/russian-trusted-cacert.pem',
			CURLOPT_CAINFO => '/var/www/cert/sbp_sber_qr/russian-trusted-cacert.pem',
            CURLOPT_SSLCERT => $this->getBusinessValue($payment, 'CLIENT_CERT'),
            CURLOPT_SSLKEY => $this->getBusinessValue($payment, 'PRIVATE_KEY')
        ];

        curl_setopt_array($curl, $curlOptions);
		$curlResult = curl_exec($curl);
        Debug::dumpToFile(curl_error($curl));
        $response = static::processResponse($curlResult);
        curl_close($curl);

        $result->setData($response);

        return $result;
    }

    protected static function processResponse(string $response): array
    {
        $response = Json::decode($response);
        return (array) $response;
    }

    protected function verifyResponse(array $response): ServiceResult
    {
        $result = new ServiceResult();

        if(isset($response['error_code'])){

            if($response['error_code'] !== '000000'){
                $result->addError(PaySystem\Error::create($response['error_description']));
            }

        } else if (!isset($response['access_token'])) {

            $result->addError(PaySystem\Error::create('['.$response['httpCode'].'] ' . $response['httpMessage'] . ': ' . $response['moreInformation']));

        }

        return $result;
    }

    protected function getToken(Payment $payment, string $scope): ServiceResult
    {
        $result = new ServiceResult();

        $headers = [
            'accept: application/json',
            'authorization: '. $this->getHeaderAuthorization($payment),
            'content-type: application/x-www-form-urlencoded',
            'rquid: '. $this->getRquid()
        ];

        $body = [
            'grant_type' => 'client_credentials',
            'scope' => $scope
        ];

        $sendResult = $this->send($payment, static::API_AUTH_URL, $headers, $body, false);
        if($sendResult->isSuccess()){

            $tokenData = $sendResult->getData();

            $verifyResponseResult = $this->verifyResponse($tokenData);
            if($verifyResponseResult->isSuccess()){
                $result->setData($tokenData);
            } else {
                $result->addErrors($verifyResponseResult->getErrors());
            }

        } else {
            $result->addErrors($sendResult->getErrors());

        }

        return $result;
    }

    protected static function getFormattedDate(\Bitrix\Main\Type\Datetime $date = null): string
    {
        if(!$date){

            $date = new \Bitrix\Main\Type\Datetime();

        }

        return $date->format('Y-m-d').'T'.$date->format('h:i:s').'Z';
    }

    protected function create(Payment $payment): ServiceResult
    {
        $result = new ServiceResult();

        $order = $payment->getOrder();

        if(PriceMaths::roundPrecision($payment->getSum()) !== PriceMaths::roundPrecision($order->getPrice())){
            $result->addError(new PaySystem\Error(Loc::getMessage('SALE_HPS_SBP_SBER_ERROR_PAYMENT_SUM')));
            return $result;
        }

        $tokenResult = $this->getToken($payment, static::SCOPE_CREATE);
        if($tokenResult->isSuccess()){
            $tokenData = $tokenResult->getData();
        } else {
            $result->addErrors($tokenResult->getErrors());
            return $result;
        }

        $items = [];

        foreach($order->getBasket() as $item){

            $items[] = [
                'position_name' => $item->getField('NAME'),
                'position_count' => (int) $item->getQuantity(),
                'position_sum' => (int) (PriceMaths::roundPrecision($item->getFinalPrice()) * 100),
                'position_description' => ''
            ];

        }

        $rquid = static::getRquid();

        $headers = [
            'accept: application/json',
            'authorization: Bearer ' . $tokenData['access_token'],
            'content-type: application/json',
            'rquid: ' . $rquid
        ];

        Loader::includeModule('currency');

        $arCurrency = CurrencyTable::getList(
            [
                'select' => ['NUMCODE'],
                'filter' => [
                    '=CURRENCY' => $order->getCurrency()
                ],
                'limit' => 1
            ]
        )->fetch();

        $body = [
            'rq_uid' => $rquid,
            'rq_tm' => static::getFormattedDate(),
            'member_id' => (string) $order->getUserId(),
            'order_number' => static::getOrderId($payment),
            'order_create_date' => static::getFormattedDate($order->getDateInsert()),
            'order_params_type' => $items,
            'id_qr' => $this->getBusinessValue($payment, 'TERMINAL_ID'),
            'order_sum' => (int) (PriceMaths::roundPrecision($order->getPrice()) * 100),
            'currency' => (string) $arCurrency['NUMCODE'],
            'description' => Loc::getMessage(
                'SALE_HPS_SBP_SBER_TRANSACTION_ID',
                ['#ORDER_NUMBER#' => $order->getField('ACCOUNT_NUMBER')]
            ),
            'sbp_member_id' => static::SPB_MEMBER_ID
        ];

        $sendResult = $this->send($payment, static::API_CREATE_URL, $headers, $body);
        if($sendResult->isSuccess()){

            $createdOrderData = $sendResult->getData();
            $verifyResponseResult = $this->verifyResponse($createdOrderData);
            if($verifyResponseResult->isSuccess()){
                $result->setData($createdOrderData);
            } else {
                $result->addErrors($verifyResponseResult->getErrors());
            }


        } else {
            $result->addErrors($sendResult->getErrors());
        }

        return $result;

    }

    protected function getStatus(Payment $payment): ServiceResult
    {
        $result = new ServiceResult();

        $tokenResult = $this->getToken($payment, static::SCOPE_STATUS);
        if($tokenResult->isSuccess()){
            $tokenData = $tokenResult->getData();
        } else {
            $result->addErrors($tokenResult->getErrors());
            return $result;
        }

        $rquid = static::getRquid();

        $headers = [
            'accept: application/json',
            'authorization: Bearer '. $tokenData['access_token'],
            'content-type: application/json',
            'rquid: ' . $rquid,
        ];

        $body = [
            'rq_uid' => $rquid,
            'rq_tm' => static::getFormattedDate(),
            'order_id' => $payment->getField('PS_INVOICE_ID'),
            'tid' => $this->getBusinessValue($payment, 'TERMINAL_ID'),
            'partner_order_number' => static::getOrderId($payment)
        ];

        $sendResult = $this->send($payment, static::API_STATUS_URL, $headers, $body);
        if($sendResult->isSuccess()){

            $statusData = $sendResult->getData();
            $verifyResponseResult = $this->verifyResponse($statusData);
            if($verifyResponseResult->isSuccess()){
                $result->setData($statusData);
            } else {
                $result->addErrors($verifyResponseResult->getErrors());
            }

        } else {
            $result->addErrors($sendResult->getErrors());
        }

        return $result;
    }

    protected static function getOrderId(Payment $payment): string
    {
        return $payment->getId() . '#' . hash('crc32', $payment->getSum());
    }

    protected function revoke(Payment $payment): ServiceResult
    {
        $result = new ServiceResult();

        $tokenResult = $this->getToken($payment, static::SCOPE_REVOKE);
        if($tokenResult->isSuccess()){
            $tokenData = $tokenResult->getData();
        } else {
            $result->addErrors($tokenResult->getErrors());
            return $result;
        }

        $rquid = static::getRquid();

        $headers = [
            'accept: application/json',
            'authorization: Bearer '. $tokenData['access_token'],
            'content-type: application/json',
            'rquid: ' . $rquid,
        ];

        $body = [
            'rq_uid' => $rquid,
            'rq_tm' => static::getFormattedDate(),
            'order_id' => $payment->getField('PS_INVOICE_ID')
        ];

        $sendResult = $this->send($payment, static::API_REVOKE_URL, $headers, $body);
        if($sendResult->isSuccess()){

            $revokeData = $sendResult->getData();
            $verifyResponseResult = $this->verifyResponse($revokeData);
            if($verifyResponseResult->isSuccess()){
                $result->setData($revokeData);
            } else {
                $result->addErrors($verifyResponseResult->getErrors());
            }

        } else {
            $result->addErrors($sendResult->getErrors());
        }

        return $result;
    }

    /**
     * @param Payment $payment
     * @return ServiceResult
     */
    public function cancel(Payment $payment): ServiceResult
    {
        $result = new ServiceResult();

        $revokeResult = $this->revoke($payment);
        if($revokeResult->isSuccess()){

            $revokeData = $revokeResult->getData();

            $description = Loc::getMessage('SALE_HPS_SBP_SBER_TRANSACTION_REVOKE', [
                '#ID#' => $revokeData['order_id'],
            ]);

            $fields = [
                'PS_INVOICE_ID' => $revokeData['order_id'],
                'PS_STATUS_CODE' => $revokeData['order_state'],
                'PS_STATUS_DESCRIPTION' => $description,
                'PS_SUM' => $payment->getSum(),
                'PS_STATUS' => 'N',
                'PS_CURRENCY' => $payment->getOrder()->getCurrency(),
                'PS_RESPONSE_DATE' => new \Bitrix\Main\Type\DateTime()
            ];

            foreach($fields as $key => $value){
                $payment->setField($key, $value);
            }

            $result->setPsData($fields);
        }

        return $result;
    }

    public function confirm(Payment $payment)
    {
        throw new SystemException(Loc::getMessage('SALE_PS_SERVICE_ERROR_HOLD_IS_NOT_SUPPORTED'));
    }

    public function beforeOrderCancel(OrderBase $order, Payment $payment): ServiceResult
    {
        $request = Context::getCurrent()->getRequest();

        $serviceResult = $this->processRequest($payment, $request);
        if ($serviceResult->isSuccess()){

            $status = null;
            $operationType = $serviceResult->getOperationType();

            if ($operationType === ServiceResult::MONEY_COMING){
                $status = 'Y';
            } else if ($operationType == ServiceResult::MONEY_LEAVING){
                $status = 'N';
            }

            if ($status !== null){
                $event = new Event('sale', Service::EVENT_ON_BEFORE_PAYMENT_PAID,
                    [
                        'payment' => $payment,
                        'status' => $status,
                        'pay_system_id' => $payment->getPaymentSystemId()
                    ]
                );
                $event->send();

                $paidResult = $payment->setPaid($status);
                if (!$paidResult->isSuccess()){
                    $error = 'PAYMENT SET PAID: '.join(' ', $paidResult->getErrorMessages());
                    Logger::addError(static::class.'. ProcessRequest: '.$error);

                    $serviceResult->setResultApplied(false);
                }
            }

            $psData = $serviceResult->getPsData();
            if ($psData){
                $res = $payment->setFields($psData);
                if (!$res->isSuccess()){
                    $error = 'PAYMENT SET PAID: '.join(' ', $res->getErrorMessages());
                    Logger::addError(static::class.'. ProcessRequest: '.$error);

                    $serviceResult->setResultApplied(false);
                }
            }

            $order->save();
        }

        return $serviceResult;
    }
}