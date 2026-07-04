<?php
namespace Imedia\Sms\Provider;

use Bitrix\MessageService\Sender;
use Bitrix\Main\PhoneNumber;

abstract class Base extends Sender\Base
{
    protected function getNormalizedPhoneNumber(string $phoneNumber): string
    {
        $parsedPhone = PhoneNumber\Parser::getInstance()->parse($phoneNumber);
        return $parsedPhone->format(PhoneNumber\Format::E164);
    }
}