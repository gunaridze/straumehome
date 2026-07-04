<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

$APPLICATION->SetTitle(Loc::getMessage('T_PERSONAL_TITLE'));

$APPLICATION->IncludeComponent(
    'imedia:personal',
    '',
    [],
    $component,
    ['HIDE_ICONS' => true]
);