<?php
namespace Imedia\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Imedia\Sms\Api\SmsTraffic as Api;

Loc::loadMessages(__FILE__);

class SmsTraffic extends Base implements HasPreferencesInterface, HasBalanceInterface
{
    private string $login;
    private string $password;
    private string $originator;
    private Api $client;

    private const MODULE_ID = 'imedia.sms';

    public function __construct()
    {
        $this->login = Option::get(static::MODULE_ID, $this->getId().'_login');
        $this->password = Option::get(static::MODULE_ID, $this->getId().'_password');
        $this->originator = Option::get(static::MODULE_ID, $this->getId().'_originator');
        $this->client = new Api($this->login, $this->password, $this->originator);
    }

    public function getId(): string
    {
        return Loc::getMessage('IMEDIA_SMS_SMS_TRAFFIC_ID');
    }

    public function sendMessage(array $messageFields): SendMessage
    {
        $result = new SendMessage();

        if (!$this->canUse()) {
            $result->addError(new Error(Loc::getMessage('IMEDIA_SMS_SMS_TRAFFIC_ERROR_CAN_USE')));
            return $result;
        }

        $from = $messageFields['MESSAGE_FROM'] ?: $this->originator;
        if($from === 'default'){
            $from = $this->originator;
        }

        $parameters = [
            'phones' => $this->getNormalizedPhoneNumber($messageFields['MESSAGE_TO']),
            'message' => $messageFields['MESSAGE_BODY'],
            'originator' => $from
        ];

        printr($parameters);

        $result = new SendMessage();
        $response = $this->client->send($parameters);

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
            return $result;
        }

        return $result;
    }

    public function canUse(): bool
    {
        return $this->login && $this->password;
    }

    public function getShortName(): string
    {
        return Loc::getMessage('IMEDIA_SMS_SMS_TRAFFIC_SHORT_NAME');
    }

    public function getName(): string
    {
        return Loc::getMessage('IMEDIA_SMS_SMS_TRAFFIC_NAME');
    }

    public function getFromList(): array
    {
        $data = $this->client->getSenderList();
        if ($data->isSuccess()) {
            return $data->getData();
        }

        return [];
    }

    public function getOptions(): array
    {
        return [
            [
                'ID' => 'login',
                'TITLE' => Loc::getMessage('IMEDIA_SMS_SMS_TRAFFIC_OPTION_LOGIN')
            ],
            [
                'ID' => 'password',
                'TITLE' => Loc::getMessage('IMEDIA_SMS_SMS_TRAFFIC_OPTION_PASSWORD')
            ],
            [
                'ID' => 'originator',
                'TITLE' => Loc::getMessage('IMEDIA_SMS_SMS_TRAFFIC_OPTION_ORIGINATOR')
            ]
        ];
    }

    public function getBalance(): string
    {
        $result = $this->client->getBalance();
        if (!$result->isSuccess()) {
            return implode(', ', $result->getErrorMessages());
        }

        $data = $result->getData();
        return $data['account'];
    }
}