<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Imedia\Main\Helpers\Image\Resize;

$dimensions = [
    'width' => 336,
    'height' => 223
];

$dimensionsBig = [
    'width' => 724,
    'height' => 336
];

$sizes = [
    'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_PROPORTIONAL_ALT],
    'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_PROPORTIONAL_ALT]
];

$sizesBig = [
    'DEFAULT' => [$dimensionsBig['width'], $dimensionsBig['height'], BX_RESIZE_IMAGE_PROPORTIONAL_ALT],
    'DEFAULT_2X' => [$dimensionsBig['width'] * 2, $dimensionsBig['height'] * 2, BX_RESIZE_IMAGE_PROPORTIONAL_ALT]
];

foreach($arResult['ITEMS'] as $key => $arItem){

    $arResult['ITEMS'][$key]['PREVIEW_PICTURE'] = Resize::setSelfResizeArray(
        $arItem['PREVIEW_PICTURE'],
        ($key > 0) ? $sizes : $sizesBig
    );

    $arResult['ITEMS'][$key]['TAGS'] = explode(', ', $arItem['TAGS']);

}