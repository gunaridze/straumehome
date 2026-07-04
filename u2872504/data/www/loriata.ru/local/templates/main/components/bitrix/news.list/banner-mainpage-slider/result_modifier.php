<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Imedia\Main\Helpers\Image\Resize;

if(!empty($arResult['ITEMS'])){

    $dimensions = [
        'width' => 1112,
        'height' => 610
    ];

    $sizes = [
        'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
        'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
    ];

    foreach($arResult['ITEMS'] as $key => $arItem){

        $arResult['ITEMS'][$key]['PREVIEW_PICTURE'] = Resize::setSelfResizeArray(
            $arItem['PREVIEW_PICTURE'],
            $sizes
        );

    }

}