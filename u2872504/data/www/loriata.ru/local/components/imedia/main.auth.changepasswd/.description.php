<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    "NAME" => Loc::getMessage("T_IMEDIA_CHANGE_PASSWORD_DESC_NAME"),
    "DESCRIPTION" => "",
    "PATH" => [
        "ID" => "imedia",
        "NAME" => Loc::getMessage("T_IMEDIA_DESC_GROUP_NAME"),
    ],
    "CACHE_PATH" => "N",
    "COMPLEX" => "N",
];