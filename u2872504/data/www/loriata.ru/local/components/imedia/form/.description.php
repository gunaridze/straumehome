<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    "NAME" => Loc::getMessage("T_IM_FORM_DESC_NAME"),
    "DESCRIPTION" => "",
    "PATH" => [
        "ID" => "im",
        "NAME" => Loc::getMessage("T_IM_DESC_GROUP_NAME"),
    ],
    "CACHE_PATH" => "N",
    "COMPLEX" => "N",
];