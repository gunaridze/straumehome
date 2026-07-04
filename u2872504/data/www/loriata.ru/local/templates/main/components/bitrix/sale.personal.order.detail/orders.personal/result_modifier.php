<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {die();}

use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Image\Resize;

$dimensions = [
    'width' => 100,
    'height' => 126
];

$sizes = [
    'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
    'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
];

$arResult['ORDER_PROPS'] = array_column($arResult['ORDER_PROPS'], NULL, 'CODE');

foreach ($arResult['BASKET'] as &$arItem){

    $picture = $arItem['PREVIEW_PICTURE'] ?: $arItem['DETAIL_PICTURE'];
    if(!$picture){
        continue;
    }

    $arItem['PICTURE'] = Resize::setSelfResizeArray(
        $picture,
        $sizes
    );
}
unset($arItem);

$arResult['ORDER_TITLE'] = Loc::getMessage('SPOD_ORDER_NUM'). ' ' .$arResult['ACCOUNT_NUMBER'];

$this->__component->SetResultCacheKeys(['ORDER_TITLE']);