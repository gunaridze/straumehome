<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Профиль");
?>
<?php if(\Bitrix\Main\Engine\CurrentUser::get()->getId() > 0): ?>
    <?$APPLICATION->IncludeComponent(
        "bitrix:sale.personal.section",
        "personal",
        array(
            "ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS" => array(
                0 => "0",
            ),
            "ACCOUNT_PAYMENT_SELL_CURRENCY" => "RUB",
            "ACCOUNT_PAYMENT_SELL_SHOW_FIXED_VALUES" => "Y",
            "ACCOUNT_PAYMENT_SELL_TOTAL" => array(
                0 => "100",
                1 => "200",
                2 => "500",
                3 => "1000",
                4 => "5000",
                5 => "",
            ),
            "ACCOUNT_PAYMENT_SELL_USER_INPUT" => "Y",
            "ACTIVE_DATE_FORMAT" => "j F Y",
            "ALLOW_INNER" => "N",
            "CACHE_GROUPS" => "Y",
            "CACHE_TIME" => "3600",
            "CACHE_TYPE" => "A",
            "CHECK_RIGHTS_PRIVATE" => "N",
            "COMPATIBLE_LOCATION_MODE_PROFILE" => "N",
            "COMPONENT_TEMPLATE" => "personal",
            "CUSTOM_PAGES" => "",
            "CUSTOM_SELECT_PROPS" => array(
                0 => "PROPERTY_STYLE",
                1 => "PROPERTY_ARTICLE",
                2 => "",
            ),
            "MAIN_CHAIN_NAME" => "",
            "NAV_TEMPLATE" => "catalog",
            "ONLY_INNER_FULL" => "N",
            "ORDERS_PER_PAGE" => "20",
            "ORDER_DEFAULT_SORT" => "STATUS",
            "ORDER_DISALLOW_CANCEL" => "N",
            "ORDER_HIDE_USER_INFO" => array(
                0 => "0",
            ),
            "ORDER_HISTORIC_STATUSES" => array(
                0 => "F",
            ),
            "ORDER_REFRESH_PRICES" => "N",
            "ORDER_RESTRICT_CHANGE_PAYSYSTEM" => array(
                0 => "F",
            ),
            "PATH_TO_BASKET" => "",
            "PATH_TO_CATALOG" => "/catalog/",
            "PATH_TO_CONTACT" => "",
            "PATH_TO_PAYMENT" => "",
            "PROFILES_PER_PAGE" => "20",
            "SAVE_IN_SESSION" => "Y",
            "SEF_FOLDER" => "/personal/",
            "SEF_MODE" => "Y",
            "SEND_INFO_PRIVATE" => "N",
            "SET_TITLE" => "Y",
            "SHOW_ACCOUNT_COMPONENT" => "Y",
            "SHOW_ACCOUNT_PAGE" => "N",
            "SHOW_ACCOUNT_PAY_COMPONENT" => "Y",
            "SHOW_BASKET_PAGE" => "N",
            "SHOW_CONTACT_PAGE" => "N",
            "SHOW_ORDER_PAGE" => "Y",
            "SHOW_PRIVATE_PAGE" => "Y",
            "SHOW_PROFILE_PAGE" => "N",
            "SHOW_SUBSCRIBE_PAGE" => "Y",
            "USE_AJAX_LOCATIONS_PROFILE" => "N",
            "PROP_1" => array(
            ),
            "SEF_URL_TEMPLATES" => array(
                "index" => "/personal/",
                "orders" => "orders/",
                "account" => "",
                "subscribe" => "subscribe/",
                "profile" => "",
                "profile_detail" => "",
                "private" => "private/",
                "order_detail" => "orders/#ID#/",
                "order_cancel" => "cancel/#ID#/",
            )
        ),
        false
    );?>
<?php else: ?>
    <auth></auth>
<?php endif ?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>