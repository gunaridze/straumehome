<?php
namespace Imedia\Main\Models\VerifyByPhone;

use Bitrix\Main\ORM;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;
use Bitrix\Main\PhoneNumber;

class VerifyByPhoneTable extends Entity\DataManager
{
    public static function getObjectClass()
    {
        return VerifyByPhone::class;
    }

    public static function getTableName()
    {
        return 'imedia_verify_by_phone';
    }

    public static function getMap()
    {
        return [
            new Fields\IntegerField('USER_ID', [
                'primary' => true
            ]),

            new Fields\StringField('PHONE_NUMBER'),

            new Fields\SecretField('OTP_SECRET', [
                'crypto_enabled' => static::cryptoEnabled('OTP_SECRET')
            ]),

            new Fields\BooleanField('USED'),

            new Fields\IntegerField('ATTEMPTS', [
                'default_value' => 0
            ]),

            new Fields\DatetimeField('DATE'),

            (new Fields\Relations\Reference(
                'USER',
                UserTable::class,
                Join::on('this.USER_ID', 'ref.ID')
            ))->configureJoinType('inner')
        ];
    }

    public static function onBeforeAdd(ORM\Event $event)
    {
        return static::modifyFields($event);
    }

    public static function onBeforeUpdate(ORM\Event $event)
    {
        return static::modifyFields($event);
    }

    protected static function modifyFields(ORM\Event $event)
    {
        $fields = $event->getParameter('fields');
        $result = new ORM\EventResult();
        $modifiedFields = [];

        if(isset($fields['PHONE_NUMBER'])){
            $modifiedFields['PHONE_NUMBER'] = static::normalizePhoneNumber($fields['PHONE_NUMBER']);
        }

        $result->modifyFields($modifiedFields);

        return $result;
    }

    public static function normalizePhoneNumber($number, $defaultCountry = '')
    {
        $phoneNumber = PhoneNumber\Parser::getInstance()->parse($number, $defaultCountry);
        return $phoneNumber->format(PhoneNumber\Format::E164);
    }
}