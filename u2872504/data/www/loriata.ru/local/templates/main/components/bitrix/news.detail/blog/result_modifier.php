<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\Uri;

$listLink = str_replace(['#SITE_DIR#', '//'], [SITE_DIR, '/'], $arResult['LIST_PAGE_URL']);

if($arResult['FIELDS']['TAGS']){

    $tags = explode(', ', $arResult['FIELDS']['TAGS']);

    $arResult['TAGS'] = [];

    foreach($tags as $tag){

        $uri = new Uri($listLink);
        $uri->addParams(
            [
                'tags' => $tag
            ]
        );

        $arResult['TAGS'][] = [
            'LINK' => $uri->getUri(),
            'LABEL' => $tag
        ];

    }

}

$arResult['ARTICLES'] = $arResult['PROPERTIES']['ARTICLES']['VALUE'];

$this->__component->SetResultCacheKeys(['ARTICLES']);
