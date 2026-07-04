<?php
namespace Imedia\Main\Models\CouponEmail;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Sale\Internals\DiscountCouponTable;

class CouponEmailTable extends Entity\DataManager
{
    public static function getObjectClass()
    {
        return CouponEmail::class;
    }

    public static function getTableName()
    {
        return 'imedia_coupon_email';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new Fields\StringField('EMAIL'),

            new Fields\IntegerField('COUPON_ID'),

            (new Fields\Relations\Reference(
                'COUPON',
                DiscountCouponTable::class,
                Join::on('this.COUPON_ID', 'ref.ID')
            ))->configureJoinType('inner')
        ];
    }
}