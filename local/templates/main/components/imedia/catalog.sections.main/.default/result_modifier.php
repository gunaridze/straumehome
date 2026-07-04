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

if(!empty($arResult)){

    $dimensions = [
        'MOBILE' => [
            'width' => 440,
            'height' => 420
        ],
        'DEFAULT' => [
            'width' => 465,
            'height' => 610
        ]
    ];

    $mobileDimensions = [
        'width' => 440,
        'height' => 420
    ];

    foreach($dimensions as $type => $typeDimensions){

        $sizes[$type] = [
            $typeDimensions['width'],
            $typeDimensions['height'],
            BX_RESIZE_IMAGE_PROPORTIONAL_ALT
        ];

        $sizes[$type . '_2X'] = [
            $typeDimensions['width'] * 2,
            $typeDimensions['height'] * 2,
            BX_RESIZE_IMAGE_PROPORTIONAL_ALT
        ];

    }

    $mobileSizes = [
        'DEFAULT' => [
            $mobileDimensions['width'],
            $mobileDimensions['height'],
            BX_RESIZE_IMAGE_PROPORTIONAL_ALT
        ],
        'DEFAULT_2X' => [
            $mobileDimensions['width'] * 2,
            $mobileDimensions['height'] * 2,
            BX_RESIZE_IMAGE_PROPORTIONAL_ALT
        ]
    ];

    foreach($arResult as $key => $arSection){

        $arResult[$key]['PICTURE'] = Resize::setSelfResizeArray(
            $arSection['PICTURE'],
            $sizes
        );

        $arResult[$key]['DETAIL_PICTURE'] = Resize::setSelfResizeArray(
            $arSection['DETAIL_PICTURE'],
            $mobileSizes
        );

    }
}
