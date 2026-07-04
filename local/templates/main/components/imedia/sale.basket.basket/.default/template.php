<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @var string $templateName
 * @var CMain $APPLICATION
 * @var CBitrixBasketComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $giftParameters
 */

$currencyFormat = [];
foreach($arResult['CURRENCIES'] as $arCurrency){
    if($arCurrency['CURRENCY'] === $arResult['CURRENCY']){
        $currencyFormat = $arCurrency['FORMAT'];
        break;
    }
}

$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'sale.basket.basket');
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.basket.basket');

$params = [
    'result' => $arResult,
    'params' => $arParams,
    'template' => $signedTemplate,
    'signedParamsString' => $signedParams,
    'siteId' => $component->getSiteId(),
    'siteTemplateId' => $component->getSiteTemplateId(),
    'templateFolder' => $templateFolder,
    'currency' => $arResult['CURRENCY'],
    'currencyFormat' => [
        'pattern' => $currencyFormat['FORMAT_STRING'],
        'decPoint' => $currencyFormat['DEC_POINT'],
        'thousandsSep' => $currencyFormat['THOUSANDS_SEP'],
        'decimals' => $currencyFormat['DECIMALS']
    ]
];

echo \Bitrix\Main\Web\Json::encode($params);