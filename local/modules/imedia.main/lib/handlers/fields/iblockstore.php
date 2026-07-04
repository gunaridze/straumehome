<?php
namespace Imedia\Main\Handlers\Fields;

use Bitrix\Main\Loader;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Localization\Loc;

class IblockStore
{

    protected static ?array $list = null;

    public static function GetUserTypeDescription()
    {
        return [
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => "store",
            "DESCRIPTION" => Loc::getMessage('IMEDIA_FIELD_IBLOCK_STORE_DESCRIPTION'),
            "GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
            "GetPropertyFieldHtmlMulty" => [__CLASS__, "GetPropertyFieldHtmlMulty"]
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $list = static::getList();

        $selectedValue = $value['VALUE'] ?: $arProperty['DEFAULT_VALUE'];

        echo \SelectBoxFromArray(
            $strHTMLControlName['VALUE'],
            [
                'REFERENCE' => array_values($list),
                'REFERENCE_ID' => array_keys($list)
            ],
            $selectedValue,
            '',
            '',
            false,
            $strHTMLControlName['FORM_NAME']
        );
    }

    public static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName)
    {
        $list = static::getList();

        $selectedValues = [];

        if($value && is_array($value)){
            foreach($value as $item){
                $selectedValues[] = $item['VALUE'];
            }
        } else {
            $selectedValues[] = $arProperty['DEFAULT_VALUE'];
        }

        echo \SelectBoxMFromArray(
            $strHTMLControlName['VALUE'] . '[]',
            [
                'REFERENCE' => array_values($list),
                'REFERENCE_ID' => array_keys($list)
            ],
            $selectedValues,
            '',
            '',
            false,
            $strHTMLControlName['FORM_NAME']
        );
    }

    protected static function getList(): array
    {
        if(gettype(static::$list) === 'NULL'){

            $list = [];

            if(Loader::includeModule('catalog')){
                $query = StoreTable::getList([
                    'order' => ['SORT' => 'ASC']
                ]);
                while($row = $query->fetch()){
                    $list[$row['ID']] = '[' . $row['ID'] . '] ' . $row['TITLE'];
                }
            }

            static::$list = $list;

        }

        return static::$list;
    }

}