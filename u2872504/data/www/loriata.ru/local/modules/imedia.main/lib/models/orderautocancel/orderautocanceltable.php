<?php
namespace Imedia\Main\Models\OrderAutoCancel;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM;
use Bitrix\Sale\Internals\OrderTable;

class OrderAutoCancelTable extends Entity\DataManager
{
    public static function getObjectClass()
    {
        return OrderAutoCancel::class;
    }

    public static function getTableName()
    {
        return 'imedia_order_auto_cancel';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            (new ORM\Fields\IntegerField('ORDER_ID', [
                'required' => true
            ])),

            (new ORM\Fields\Relations\Reference(
                'ORDER',
                OrderTable::class,
                ORM\Query\Join::on('this.ORDER_ID', 'ref.ID')
            ))->configureJoinType('inner'),

            new ORM\Fields\DateTimeField('DATE', ['required' => true]),

            new ORM\Fields\TextField('REASON')
        ];
    }
}