<?php
namespace Imedia\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Sender\Result\SendMessage;

Loc::loadMessages(__FILE__);

class Fake extends Base
{
    public function __construct()
    {
        
    }

    public function getId(): string
    {
        return Loc::getMessage('IMEDIA_SMS_FAKE_ID');
    }

    public function sendMessage(array $messageFields): SendMessage
    {
        return new SendMessage();
    }

    public function canUse(): bool
    {
        return true;
    }

    public function getShortName(): string
    {
        return Loc::getMessage('IMEDIA_SMS_FAKE_SHORT_NAME');
    }

    public function getName(): string
    {
        return Loc::getMessage('IMEDIA_SMS_FAKE_NAME');
    }

    public function getFromList(): array
    {
        return [
            [
                'id' => '1',
                'name' => 'John Doe'
            ]
        ];
    }
}