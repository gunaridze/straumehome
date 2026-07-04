<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Imedia\Main\Helpers\Image\Resize;

$dimensions = [
    'width' => 128,
    'height' => 128
];

$sizes = [
    'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
    'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
];

foreach($arResult['SECTIONS'] as &$arSection){

    $arSection['PICTURE'] = Resize::setSelfResizeArray(
        $arSection['PICTURE'],
        $sizes
    );

}
unset($arSection);