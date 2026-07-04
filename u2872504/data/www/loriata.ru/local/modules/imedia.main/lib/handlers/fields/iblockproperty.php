<?php
namespace Imedia\Main\Handlers\Fields;

use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;

class IblockProperty
{

    protected static ?array $list = null;

    public static function GetUserTypeDescription()
    {
        return [
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => "property",
            "DESCRIPTION" => Loc::getMessage('IMEDIA_FIELD_IBLOCK_PROPERTY_DESCRIPTION'),
            "GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
            "GetPropertyFieldHtmlMulty" => [__CLASS__, "GetPropertyFieldHtmlMulty"],
            "GetSettingsHTML" => [__CLASS__, "GetSettingsHTML"]
        ];
    }

    public static function GetSettingsHTML(array $arProperty, array $strHTMLControlName, array &$arPropertyFields)
    {
        $html = '';

        $html .= '<tr>';
        $html .= '<td width="40%">'. Loc::getMessage('BT_ADM_IEP_PROP_LINK_IBLOCK') .'</td>';
        $html .= '<td>';
        $html .= GetIBlockDropDownList(
            $arProperty['LINK_IBLOCK_ID'],
            "PROPERTY_LINK_IBLOCK_TYPE_ID",
            "PROPERTY_LINK_IBLOCK_ID",
            [],
            'class="adm-detail-iblock-types"',
            'class="adm-detail-iblock-list"'
        );
        $html .= '</td>';
        $html .= '</tr>';

        return $html;
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $list = static::getList((int) $arProperty['LINK_IBLOCK_ID']);

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
        $list = static::getList((int) $arProperty['LINK_IBLOCK_ID']);

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

    protected static function getList(int $iblockId): array
    {
        if(gettype(static::$list) === 'NULL'){

            $list = [];

            if(
                ($iblockId > 0)
                && Loader::includeModule('iblock')
            ){

                $query = PropertyTable::getList(
                    [
                        'select' => [
                            'CODE', 'NAME'
                        ],
                        'filter' => [
                            '=IBLOCK_ID' => $iblockId,
                            '!CODE' => false
                        ],
                        'order' => [
                            'NAME' => 'ASC'
                        ]
                    ]
                );
                while($row = $query->fetch()){
                    $list[$row['CODE']] = $row['NAME'] . ' ['.$row['CODE'].']';
                }

            }

            static::$list = $list;

        }

        return static::$list;
    }

}