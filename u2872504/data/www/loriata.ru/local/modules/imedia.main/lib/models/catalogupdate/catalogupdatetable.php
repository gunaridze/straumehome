<?php
namespace Imedia\Main\Models\CatalogUpdate;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields;

class CatalogUpdateTable extends Entity\DataManager
{
    public static function getObjectClass()
    {
        return CatalogUpdate::class;
    }

    public static function getCollectionClass()
    {
        return CatalogUpdates::class;
    }

    public static function getTableName()
    {
        return 'imedia_catalog_update';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new Fields\StringField('INTERNAL_CODE'),

            new Fields\ArrayField('DATA')
        ];
    }
}