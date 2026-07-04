<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if ($arParams["MAIN_CHAIN_NAME"] <> ''){
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_SUBSCRIBE_NEW"));

if ($arParams['SET_TITLE'] === 'Y'){
	$APPLICATION->SetTitle(Loc::getMessage("SPS_TITLE_SUBSCRIBE"));
}
?>
<subscription-list></subscription-list>