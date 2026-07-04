<?php
namespace Imedia\Sms\Api;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Localization\Loc;

class SmsTraffic
{
    private string $login;
    private string $password;
    private ?string $originator;

    const API_URL = 'https://api.smstraffic.ru/multi.php';

    public function __construct(string $login, string $password, string $originator = null)
    {
        $this->login = $login;
        $this->password = $password;
        $this->originator = $originator;
    }

    public function getSenderList(): Result
    {
        $result = new Result();

        $result->setData(
            [
                [
                    'id' => 'default',
                    'name' => Loc::getMessage('IMEDIA_SMS_SMS_TRAFFIC_FROM_CONFIG')
                ]
            ]
        );

        return $result;
    }

    private function query(array $parameters = [], string $httpMethod = HttpClient::HTTP_POST): Result
    {
        $result = new Result();

        $uri = new Uri(static::API_URL);

        $httpClient = new HttpClient(
            [
                'redirect' => true,
                'redirectMax' => 5,
                'waitResponse' => true,
                'socketTimeout' => 2,
                'streamTimeout' => 3,
                'version' => HttpClient::HTTP_1_1,
                'proxyHost' => '',
                'proxyPort' => '',
                'proxyUser' => '',
                'proxyPassword' => '',
                'compress' => false,
                'charset' => '',
                'disableSslVerification' => true
            ]
        );

        $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded', true);

        $parameters['login'] = $this->login;
        $parameters['password'] = $this->password;

        if ($httpMethod === HttpClient::HTTP_GET) {

            if(!empty($parameters)){
                $uri->addParams($parameters);
            }

            $isSuccess = $httpClient->query($httpMethod, $uri->getUri());

        } else {
            $isSuccess = $httpClient->query($httpMethod, $uri->getUri(), $parameters);
        }

        if(!$isSuccess){
            $responseError = $httpClient->getError();
            $errorMessage = '('.$responseError['httpStatusCode'].') '
                . $responseError['status']
                . ': '
                . $responseError['errorMessage'];
            $result->addError( new Error($errorMessage) );

            return $result;
        }

        $response = $this->parseResponse($httpClient->getResult());

        if(strtoupper($response['result']) === 'ERROR'){
            $result->addError(new Error($response['description'], $response['code']));
            return $result;
        }

        $result->setData($response);

        return $result;
    }

    protected function parseResponse(string $result): array
    {
        $xml = simplexml_load_string($result);
        $data = Json::decode(Json::encode($xml));
        return $data;
    }

    public function send(array $parameters): Result
    {
        $result = new Result();

        $response = $this->query($parameters);

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        $result->setData($response->getData());

        return $result;
    }

    public function getBalance(): Result
    {
        $result = new Result();

        $response = $this->query([
            'operation' => 'account'
        ]);

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        $result->setData($response->getData());

        return $result;
    }
}