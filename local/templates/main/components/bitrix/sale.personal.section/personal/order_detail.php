<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_ORDER_PAGE'] !== 'Y'){
	LocalRedirect($arParams['SEF_FOLDER']);
}

if ($arParams["MAIN_CHAIN_NAME"] <> ''){
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ORDERS"), $arResult['PATH_TO_ORDERS']);
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ORDER_DETAIL", array("#ID#" => urldecode($arResult["VARIABLES"]["ID"]))));

?>
<?php ob_start()?>
<div class="profile-page__top">
    <div class="title page__title"><?=$APPLICATION->GetTitle(false)?></div>
    <a href="<?=$arParams['SEF_FOLDER'].$arParams['SEF_URL_TEMPLATES']['orders']?>" class="back-link">
        <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7.97474 15.6829C8.13307 15.6829 8.29141 15.6246 8.41641 15.4996C8.65807 15.2579 8.65807 14.8579 8.41641 14.6163L3.79974 9.99961L8.41641 5.38294C8.65807 5.14128 8.65807 4.74128 8.41641 4.49961C8.17474 4.25794 7.77474 4.25794 7.53307 4.49961L2.47474 9.55794C2.23307 9.79961 2.23307 10.1996 2.47474 10.4413L7.53307 15.4996C7.65807 15.6246 7.81641 15.6829 7.97474 15.6829Z" fill="#479458" />
            <path d="M3.05703 10.625H17.082C17.4237 10.625 17.707 10.3417 17.707 10C17.707 9.65833 17.4237 9.375 17.082 9.375H3.05703C2.71537 9.375 2.43203 9.65833 2.43203 10C2.43203 10.3417 2.71537 10.625 3.05703 10.625Z" fill="#479458" />
        </svg>
        <?=Loc::getMessage('SPS_BACK_TO_LIST')?>
    </a>
</div>
<?php
$APPLICATION->addViewContent('title', ob_get_clean());
$APPLICATION->SetPageProperty('title-type', 'removed');

$arDetParams = [
    "PATH_TO_LIST" => $arResult["PATH_TO_ORDERS"],
    "PATH_TO_CANCEL" => $arResult["PATH_TO_ORDER_CANCEL"],
    "PATH_TO_COPY" => $arResult["PATH_TO_ORDER_COPY"],
    "PATH_TO_PAYMENT" => $arParams["PATH_TO_PAYMENT"],
    "SET_TITLE" => $arParams["SET_TITLE"],
    "ID" => $arResult["VARIABLES"]["ID"],
    "ACTIVE_DATE_FORMAT" => $arParams["ACTIVE_DATE_FORMAT"],
    "ALLOW_INNER" => $arParams["ALLOW_INNER"],
    "ONLY_INNER_FULL" => $arParams["ONLY_INNER_FULL"],
    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
    "CACHE_TIME" => $arParams["CACHE_TIME"],
    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
    "RESTRICT_CHANGE_PAYSYSTEM" => $arParams["ORDER_RESTRICT_CHANGE_PAYSYSTEM"],
    "DISALLOW_CANCEL" => $arParams["ORDER_DISALLOW_CANCEL"],
    "REFRESH_PRICES" => $arParams["ORDER_REFRESH_PRICES"],
    "HIDE_USER_INFO" => $arParams["ORDER_HIDE_USER_INFO"],

    "CUSTOM_SELECT_PROPS" => $arParams["CUSTOM_SELECT_PROPS"]
];

foreach($arParams as $key => $val)	{
    if(mb_strpos($key, "PROP_") !== false)
        $arDetParams[$key] = $val;
}

$APPLICATION->IncludeComponent(
    "bitrix:sale.personal.order.detail",
    "orders.personal",
    $arDetParams,
    $component
);
