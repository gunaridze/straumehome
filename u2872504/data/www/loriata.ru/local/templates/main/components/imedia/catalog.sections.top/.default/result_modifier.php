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

if(!empty($arResult)){
    $dimensions = [
        'width' => 100,
        'height' => 100
    ];

    $sizes = [
        'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_PROPORTIONAL_ALT],
        'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_PROPORTIONAL_ALT]
    ];

    foreach($arResult as $key => $arSection){
        $arResult[$key]['PICTURE'] = \Imedia\Main\Helpers\Image\Resize::setSelfResizeArray(
            $arSection['PICTURE'],
            $sizes
        );
    }
}

