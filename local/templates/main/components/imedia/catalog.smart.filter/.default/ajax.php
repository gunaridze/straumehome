<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;

$APPLICATION->RestartBuffer();
unset($arResult['COMBO']);
echo Json::encode($arResult);