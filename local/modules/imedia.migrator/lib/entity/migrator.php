<?php

namespace Imedia\Migrator\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

class MigratorTable extends DataManager
{
    public static function getTableName()
    {
        return 'imedia_migrator_table';
    }

    public static function getMap()
    {
        return [
            new Fields\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new Fields\StringField('MIGRATION', [
                'required' => true
            ]),
        ];
    }
}