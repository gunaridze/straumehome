<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\Uri;
use Imedia\Main\Helpers\Image\Resize;

if(!empty($arResult['ITEMS'])){

    $dimensions = [
        'SMALL' => [
            'width' => 336,
            'height' => 223
        ],
        'DEFAULT' => [
            'width' => 724,
            'height' => 336
        ]
    ];

    $sizes = [
        'SMALL' => [
            'DEFAULT' => [$dimensions['SMALL']['width'], $dimensions['SMALL']['height'], BX_RESIZE_IMAGE_EXACT],
            'DEFAULT_2X' => [$dimensions['SMALL']['width'] * 2, $dimensions['SMALL']['height'] * 2, BX_RESIZE_IMAGE_EXACT]
        ],
        'DEFAULT' => [
            'DEFAULT' => [$dimensions['DEFAULT']['width'], $dimensions['DEFAULT']['height'], BX_RESIZE_IMAGE_EXACT],
            'DEFAULT_2X' => [$dimensions['DEFAULT']['width'] * 2, $dimensions['DEFAULT']['height'] * 2, BX_RESIZE_IMAGE_EXACT]
        ]
    ];

    $listLink = str_replace(['#SITE_DIR#', '//'], [SITE_DIR, '/'], $arResult['LIST_PAGE_URL']);

    $typeMap = [
        'DEFAULT',
        'SMALL',
        'SMALL',
        'SMALL',
        'SMALL',
        'SMALL',
        'DEFAULT',
        'SMALL',
        'SMALL',
        'SMALL',
        'DEFAULT',
        'SMALL',
        'SMALL'
    ];

    $typeMapKey = -1;

    foreach ($arResult['ITEMS'] as $key => $arItem) {

        $typeMapKey++;
        if(!isset($typeMap[$typeMapKey])){
            $typeMapKey = 0;
        }

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
            $sizes[$typeMap[$typeMapKey]]
        );

    }

}