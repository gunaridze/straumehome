<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\Uri;
use Imedia\Main\Helpers\Image\Resize;

if(!empty($arResult['ITEMS'])){

    $dimensions = [
        'width' => 336,
        'height' => 223
    ];

    $sizes = [
        'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
        'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
    ];

    $listLink = str_replace(['#SITE_DIR#', '//'], [SITE_DIR, '/'], $arResult['LIST_PAGE_URL']);

    foreach ($arResult['ITEMS'] as $key => $arItem) {

        $arResult['ITEMS'][$key]['TAGS'] = [];

        if($arItem['FIELDS']['TAGS']){

            $tags = explode(', ', $arItem['FIELDS']['TAGS']);

            foreach($tags as $tag){

                $uri = new Uri($listLink);
                $uri->addParams(
                    [
                        'tags' => $tag
                    ]
                );

                $arResult['ITEMS'][$key]['TAGS'][] = [
                    'LINK' => $uri->getUri(),
                    'LABEL' => $tag
                ];

            }

        }

        $arResult['ITEMS'][$key]['PREVIEW_PICTURE'] = Resize::setSelfResizeArray(
            $arItem['PREVIEW_PICTURE'],
            $sizes
        );

    }

}