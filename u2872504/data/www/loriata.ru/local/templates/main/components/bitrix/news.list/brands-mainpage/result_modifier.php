<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Imedia\Main\Helpers\Image\Resize;

$dimensions = [
    'width' => 210,
    'height' => 210
];

$sizes = [
    'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_PROPORTIONAL_ALT],
    'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_PROPORTIONAL_ALT]
];

foreach($arResult['ITEMS'] as $key => $arItem){

    $arResult['ITEMS'][$key]['PREVIEW_PICTURE'] = Resize::setSelfResizeArray(
        $arItem['PREVIEW_PICTURE'],
        $sizes
    );

    $arResult['ITEMS'][$key]['DETAIL_PAGE_URL'] = $arItem['DETAIL_PAGE_URL'] . $arParams['SELECTED_SECTION_PATH'] . '/';

}