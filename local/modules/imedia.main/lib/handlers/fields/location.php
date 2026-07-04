<?php
namespace Imedia\Main\Handlers\Fields;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Location {

    protected const USER_TYPE_ID = 'location';

    public static function GetUserTypeDescription()
    {
        return [
            'CLASS_NAME' => __CLASS__,
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
            'USER_TYPE_ID' => static::USER_TYPE_ID,
            'DESCRIPTION' => Loc::getMessage('IMEDIA_FIELD_LOCATION_DESCRIPTION')
        ];
    }

    public static function GetDBColumnType()
    {
        return 'text';
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        Loader::includeModule('sale');

        ob_start();
        global $APPLICATION;
        $APPLICATION->IncludeComponent(
            "bitrix:sale.location.selector.search",
            "",
            [
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "A",
                "CODE" => $arHtmlControl['VALUE'],
                "FILTER_BY_SITE" => "N",
                "ID" => "",
                "INITIALIZE_BY_GLOBAL_EVENT" => "",
                "INPUT_NAME" => $arHtmlControl['NAME'],
                "JS_CALLBACK" => "",
                "JS_CONTROL_GLOBAL_ID" => "",
                "PROVIDE_LINK_BY" => "code",
                "SHOW_DEFAULT_LOCATIONS" => "N",
                "SUPPRESS_ERRORS" => "N"
            ]
        );
        return ob_get_clean();
    }
}