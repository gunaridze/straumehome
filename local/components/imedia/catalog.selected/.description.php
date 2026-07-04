<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    "NAME" => Loc::getMessage("T_IMEDIA_CATALOG_SELECTED_DESC_NAME"),
    "DESCRIPTION" => "",
    "PATH" => [
        "ID" => "imedia",
        "NAME" => Loc::getMessage("T_IMEDIA_CATALOG_SELECTED_DESC_GROUP_NAME"),
    ],
    "CACHE_PATH" => "N",
    "COMPLEX" => "N",
];