<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\Uri;
use Imedia\Main\Helpers\Image\Resize;

if(!empty($arResult['ITEMS'])){

    $dimensions = [
        'width' => 478,
        'height' => 637
    ];

    $sizes = [
        'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
        'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
    ];

    foreach ($arResult['ITEMS'] as $key => &$arItem) {

        if(isset($arItem['DISPLAY_PROPERTIES']['GALLERY']['FILE_VALUE']['ID'])){
            $arItem['DISPLAY_PROPERTIES']['GALLERY']['FILE_VALUE']
                = [$arItem['DISPLAY_PROPERTIES']['GALLERY']['FILE_VALUE']];
        }

        Resize::setSelfResizeArray(
            $arItem['DISPLAY_PROPERTIES']['GALLERY']['FILE_VALUE'],
            $sizes
        );

    }
    unset($arItem);
}