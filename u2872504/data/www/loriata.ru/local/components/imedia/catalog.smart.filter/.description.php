<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BCSF_NAME"),
	"DESCRIPTION" => GetMessage("CD_BCSF_DESCRIPTION"),
	"ICON" => "/images/iblock_filter.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 70,
    "PATH" => [
        "ID" => "imedia",
        "NAME" => Loc::getMessage("T_IMEDIA_DESC_GROUP_NAME"),
    ],
);
?>