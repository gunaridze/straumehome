<?php
namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\ServiceResult;
use Bitrix\Sale\PriceMaths;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class PaytureHandler extends PaySystem\ServiceHandler implements PaySystem\IRefund
{
    protected const TRACKING_ID_DELIMITER = '-';
    protected const STATUS_SUCCESSFUL_CODE = 'Charged';
    protected const PAYMENT_TYPE_PAY = 'Pay';
    protected const PAYMENT_TYPE_BLOCK = 'Block';
    protected const API_DOMAIN = 'payture.com';
    protected const API_VERSION = 'apim';

    /**
     * @param Payment $payment
     * @param Request|null $request
     * @return ServiceResult
     */
    public function initiatePay(Payment $payment, Request $request = null): ServiceResult
    {
        $result = new ServiceResult();

        $createPaymentTokenResult = $this->createPaymentToken($payment);
        if (!$createPaymentTokenResult->isSuccess()){
            $result->addErrors($createPaymentTokenResult->getErrors());
            return $result;
        }

        $createPaymentTokenData = $createPaymentTokenResult->getData();
        $sessionId = $createPaymentTokenData['SessionId'];

        if(!$sessionId){
            $result->addError(new PaySystem\Error(Loc::getMessage('SALE_HPS_PAYTURE_ERROR_TOKEN')));
            return $result;
        }

        $uri = $this->getApiUrl($payment, 'Pay');
        $uri->addParams(
            [
                'SessionId' => $sessionId
            ]
        );

        $result->setPaymentUrl($uri->getUri());
        $this->setExtraParams(['PAYMENT_URL' => $uri->getUri()]);

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

    /**
     * @param Payment $payment
     * @param Request $request
     * @return ServiceResult
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\ObjectException
     */
    public function processRequest(Payment $payment, Request $request): ServiceResult
    {
        $result = new ServiceResult();

        $trackingId = trim(filter_var(
            $request->get('OrderId'),
            FILTER_SANITIZE_STRING
        ));

        $payturePaymentResult = $this->getPayturePayment($payment, $trackingId);
        if ($payturePaymentResult->isSuccess()){

            $payturePaymentData = $payturePaymentResult->getData();
            if ($payturePaymentData['State'] === self::STATUS_SUCCESSFUL_CODE){

                $description = Loc::getMessage('SALE_HPS_PAYTURE_TRANSACTION', [
                    '#ID#' => $payturePaymentData['OrderId'],
                ]);

                $fields = [
                    'PS_INVOICE_ID' => $payturePaymentData['OrderId'],
                    'PS_STATUS_CODE' => $payturePaymentData['State'],
                    'PS_STATUS_DESCRIPTION' => $description,
                    'PS_SUM' => $payturePaymentData['Amount'] / 100,
                    'PS_STATUS' => 'N',
                    'PS_CURRENCY' => 'RUB',
                    'PS_RESPONSE_DATE' => new \Bitrix\Main\Type\DateTime()
                ];

                if ($this->isSumCorrect($payment, $payturePaymentData['Amount'] / 100)){
                    $fields['PS_STATUS'] = 'Y';

                    PaySystem\Logger::addDebugInfo(
                        __CLASS__.': PS_CHANGE_STATUS_PAY='.$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
                    );

                    if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y'){
                        $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
                    }
                } else {
                    $error = Loc::getMessage('SALE_HPS_PAYTURE_ERROR_SUM');
                    $fields['PS_STATUS_DESCRIPTION'] .= '. '.$error;
                    $result->addError(PaySystem\Error::create($error));
                }

                $result->setPsData($fields);

            }

        } else {
            $result->addErrors($payturePaymentResult->getErrors());
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function getPaymentIdFromRequest(Request $request): int
    {
        $trackingId = trim(filter_var(
            $request->get('OrderId'),
            FILTER_SANITIZE_STRING
        ));

        if(!$trackingId){
            return false;
        }

        list(, $paymentId, $serviceId) = explode(self::TRACKING_ID_DELIMITER, $trackingId);
        return (int) $paymentId;
    }

    /**
     * @param Request $request
     * @param $paySystemId
     * @return bool
     */
    public static function isMyResponse(Request $request, $paySystemId): bool
    {
        $paymentIdString = trim(filter_var(
            $request->get('OrderId'),
            FILTER_SANITIZE_STRING
        ));

        if(!$paymentIdString){
            return false;
        }

        list(, $paymentId, $serviceId) = explode(self::TRACKING_ID_DELIMITER, $paymentIdString);

        return (int) $serviceId === (int) $paySystemId;
    }

    /**
     * @param Payment $payment
     * @return ServiceResult
     * @throws \Bitrix\Main\ArgumentException
     */
    private function getPayturePayment(Payment $payment, string $trackingId): ServiceResult
    {
        $result = new ServiceResult();

        $params = [
            'Key' => $this->getBusinessValue($payment, 'KEY'),
            'OrderId' => $trackingId
        ];

        $sendResult = $this->send($payment, 'GetState', $params);
        if($sendResult->isSuccess()){

            $paymentTokenData = $sendResult->getData();
            $verifyResponseResult = $this->verifyResponse($paymentTokenData);
            if ($verifyResponseResult->isSuccess()){
                $result->setData($paymentTokenData);
            } else {
                $result->addErrors($verifyResponseResult->getErrors());
            }

        } else {
            $result->addErrors($sendResult->getErrors());
        }

        return $result;
    }

    /**
     * @param array $response
     * @return ServiceResult
     */
    private function verifyResponse(array $response): ServiceResult
    {
        $result = new ServiceResult();

        if($response['Success'] === 'False'){
            $result->addError(PaySystem\Error::create($response['ErrCode']));
        }

        return $result;
    }

    /**
     * @param string $command
     * @param array $params
     * @param bool $isTest
     * @return ServiceResult
     * @throws \Bitrix\Main\ArgumentException
     */
    private function send(Payment $payment, string $command, array $params = [])
    {
        $result = new ServiceResult();

        $options = [
            'redirect' => true,
            'redirectMax' => 5,
            'waitResponse' => true,
            'socketTimeout' => 3,
            'streamTimeout' => 5,
            'version' => HttpClient::HTTP_1_1,
            'proxyHost' => '',
            'proxyPort' => '',
            'proxyUser' => '',
            'proxyPassword' => '',
            'compress' => true,
            'charset' => '',
            'disableSslVerification' => false,
        ];

        $httpClient = new HttpClient($options);

        $httpClient->setHeader('Accept', 'application/xhtml+xml', true);
        $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded', true);

        $uri = $this->getApiUrl($payment, $command);

        $isSuccess = $httpClient->query(HttpClient::HTTP_POST, $uri->getUri(), $params);
        if(!$isSuccess){

            $errors = $httpClient->getError();

            foreach ($errors as $code => $message){
                $result->addError(PaySystem\Error::create($message, $code));
            }

            return $result;

        } else {
            $result->setData(static::getResponse($httpClient));
        }

        return $result;
    }

    /**
     * @param HttpClient $httpClient
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    private static function getResponse(HttpClient $httpClient): array
    {
        $xml = simplexml_load_string($httpClient->getResult(), 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = Json::encode($xml);
        $array = Json::decode($json);
        $response = $array['@attributes'];

        return (array) $response;
    }

    /**
     * @param Payment $payment
     * @param $sum
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\ObjectException
     */
    private function isSumCorrect(Payment $payment, $sum): bool
    {
        PaySystem\Logger::addDebugInfo(
            __CLASS__.': paytureSum='.PriceMaths::roundPrecision($sum)."; paymentSum=".PriceMaths::roundPrecision($payment->getSum())
        );

        return PriceMaths::roundPrecision($sum) === PriceMaths::roundPrecision($payment->getSum());
    }

    private function createPaymentToken(Payment $payment): ServiceResult
    {
        $result = new ServiceResult();

        $order = $payment->getOrder();
        $paymentSum = PriceMaths::roundPrecision($payment->getSum());

        $data = [
            'SessionType' => static::PAYMENT_TYPE_PAY,
            'OrderId' => $this->createTrackingId($payment),
            'Amount' => round($paymentSum * 100),
            'Url' => str_replace(
                ['#ORDER_ID#'],
                [$order->getId()],
                $this->getBusinessValue($payment, 'RETURN_URL')
            ),
            'Product' => Loc::getMessage(
                'SALE_HPS_PAYTURE_TRANSACTION_ID',
                ['#ORDER_NUMBER#' => $order->getField('ACCOUNT_NUMBER')]
            ),
            'Total' => $paymentSum
        ];

        $dataString = [];
        foreach($data as $key => $value){
            $dataString[] = $key . '=' . $value;
        }

        $dataString = urlencode(implode(';', $dataString));

        $params = [
            'Key' => $this->getBusinessValue($payment, 'KEY'),
            'Data' => $dataString
        ];

        $sendResult = $this->send($payment, 'Init', $params);
        if ($sendResult->isSuccess()){
            $paymentTokenData = $sendResult->getData();
            $verifyResponseResult = $this->verifyResponse($paymentTokenData);
            if ($verifyResponseResult->isSuccess()){
                $result->setData($paymentTokenData);
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
     * @param string $command
     * @return Uri
     */
    private function getApiUrl(Payment $payment, string $command): Uri
    {
        $prefixCode = ($this->getBusinessValue($payment, 'IS_TEST') === 'Y') ? 'DEV' : 'PROD';
        $prefix = $this->getBusinessValue($payment, 'ENVIRONMENT_' . $prefixCode);

        $uri = new Uri('https://'.$prefix.'.' . static::API_DOMAIN . '/' . static::API_VERSION . '/' . $command);

        return $uri;
    }

    /**
     * @param Payment $payment
     * @return string
     */
    private function createTrackingId(Payment $payment): string
    {
        return time()
            . self::TRACKING_ID_DELIMITER
            . $payment->getId()
            . self::TRACKING_ID_DELIMITER
            . $this->service->getField('ID');
    }

    public function refund(Payment $payment, $refundableSum): ServiceResult
    {
        $result = new ServiceResult();

        $params = [
            'Key' => $this->getBusinessValue($payment, 'KEY'),
            'OrderId' => $payment->getField('PS_INVOICE_ID'),
            'Password' => $this->getBusinessValue($payment, 'PASSWORD'),
        ];

        $sendResult = $this->send($payment, 'Refund', $params);
        if ($sendResult->isSuccess()){
            $refundData = $sendResult->getData();
            $verifyResponseResult = $this->verifyResponse($refundData);
            if ($verifyResponseResult->isSuccess()){
                $result->setData($refundData);
                $result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
            } else {
                $result->addErrors($verifyResponseResult->getErrors());
            }
        } else {
            $result->addErrors($sendResult->getErrors());
        }

        return $result;
    }
}