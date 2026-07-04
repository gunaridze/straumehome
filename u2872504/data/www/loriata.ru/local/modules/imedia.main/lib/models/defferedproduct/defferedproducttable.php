<?php
namespace Imedia\Main\Models\DefferedProduct;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Iblock\ElementTable;
use Imedia\Main\Helpers\Orm\Validator;
use Imedia\Main\Helpers\Iblock\Iblock;

class DefferedProductTable extends Entity\DataManager
{
    public static function getObjectClass()
    {
        return DefferedProduct::class;
    }

    public static function getCollectionClass()
    {
        return DefferedProducts::class;
    }

    public static function getTableName()
    {
        return 'imedia_deffered_product';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            (new IntegerField('ELEMENT_ID', [
                'required' => true,
                'validation' => function(){
                    return [
                        new Validator\IblockElement(Iblock::getId('CATALOG'))
                    ];
                }
            ])),

            (new Reference(
                'ELEMENT',
                ElementTable::class,
                Join::on('this.ELEMENT_ID', 'ref.ID')
            ))->configureJoinType('inner'),

            new Entity\EnumField('TYPE', [
                'values' => ['FAVORITES', 'COMPARISON']
            ]),
            new Entity\TextField('OWNER', [
                'required' => true,
            ]),

            new Entity\DateTimeField('DATE', ['required' => true]),
        ];
    }
}