<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)	die();

global $APPLICATION;

$APPLICATION->IncludeComponent(
    "imedia:sale.order.ajax",
    "",
    array(
        "ACTION_VARIABLE" => "soa-action",
        "ADDITIONAL_PICT_PROP_1" => "-",
        "ADDITIONAL_PICT_PROP_2" => "-",
        "ADDITIONAL_PICT_PROP_21" => "-",
        "ADDITIONAL_PICT_PROP_22" => "-",
        "ALLOW_APPEND_ORDER" => "Y",
        "ALLOW_AUTO_REGISTER" => "Y",
        "BASKET_IMAGES_SCALING" => "adaptive",
        "COMPATIBLE_MODE" => "Y",
        "DELIVERY_NO_AJAX" => "Y",
        "DELIVERY_NO_SESSION" => "Y",
        "DELIVERY_TO_PAYSYSTEM" => "d2p",
        "DISABLE_BASKET_REDIRECT" => "Y",
        "EMPTY_BASKET_HINT_PATH" => "/",
        "ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
        "PATH_TO_AUTH" => "/auth/",
        "PATH_TO_BASKET" => "/",
        "PATH_TO_PAYMENT" => "payment.php",
        "PATH_TO_PERSONAL" => "index.php",
        "PAY_FROM_ACCOUNT" => "N",
        "PRODUCT_COLUMNS_VISIBLE" => array(),
        "SEND_NEW_USER_NOTIFY" => "N",
        "SET_TITLE" => "N",
        "SHOW_NOT_CALCULATED_DELIVERIES" => "L",
        "SHOW_VAT_PRICE" => "Y",
        "SPOT_LOCATION_BY_GEOIP" => "N",
        "TEMPLATE_LOCATION" => "popup",
        "USER_CONSENT" => "N",
        "USER_CONSENT_ID" => "0",
        "USER_CONSENT_IS_CHECKED" => "Y",
        "USER_CONSENT_IS_LOADED" => "N",
        "USE_PHONE_NORMALIZATION" => "Y",
        "USE_PRELOAD" => "Y",
        "USE_PREPAYMENT" => "N",
        "COMPONENT_TEMPLATE" => ".default"
    ),
    false
);