<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\Json;

global $APPLICATION;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if(
    !$request->isAjaxRequest()
    && ($arParams['SET_CANONICAL'] === 'Y')
    && $arResult['SECTION_PAGE_URL']
) {
    $APPLICATION->AddHeadString('<link href="https://'.SITE_SERVER_NAME.$arResult['SECTION_PAGE_URL'].'" rel="canonical" />',true);
}

if(
    !$request->isAjaxRequest()
    && ($request->get('ASYNC') !== 'Y')
){

    $result = $arResult['CACHED_TPL'];

    $result =  preg_replace_callback(
        "/#BANNER_CATALOG_SECTION#/",
        function($matches) use ($arParams, $APPLICATION){
            ob_start();

            if($arParams['HIDE_BANNER'] !== 'Y'){
                $APPLICATION->IncludeComponent(
                    "bitrix:news.list",
                    "banner-catalog-section",
                    array(
                        "ACTIVE_DATE_FORMAT" => "d.m.y",
                        "ADD_SECTIONS_CHAIN" => "N",
                        "AJAX_MODE" => "N",
                        "AJAX_OPTION_ADDITIONAL" => "",
                        "AJAX_OPTION_HISTORY" => "N",
                        "AJAX_OPTION_JUMP" => "N",
                        "AJAX_OPTION_STYLE" => "Y",
                        "CACHE_FILTER" => "N",
                        "CACHE_GROUPS" => "Y",
                        "CACHE_TIME" => "36000000",
                        "CACHE_TYPE" => "A",
                        "CHECK_DATES" => "Y",
                        "DETAIL_URL" => "",
                        "DISPLAY_BOTTOM_PAGER" => "N",
                        "DISPLAY_DATE" => "Y",
                        "DISPLAY_NAME" => "N",
                        "DISPLAY_PICTURE" => "Y",
                        "DISPLAY_PREVIEW_TEXT" => "Y",
                        "DISPLAY_TOP_PAGER" => "N",
                        "FIELD_CODE" => array(),
                        "FILTER_NAME" => $arParams['FILTER_NAME_CATALOG_SECTION'],
                        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                        "IBLOCK_ID" => Imedia\Main\Helpers\Iblock\Iblock::getId('BANNER_CATALOG_SECTION'),
                        "IBLOCK_TYPE" => 'content',
                        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                        "INCLUDE_SUBSECTIONS" => "N",
                        "MESSAGE_404" => "",
                        "NEWS_COUNT" => "1",
                        "PAGER_BASE_LINK_ENABLE" => "N",
                        "PAGER_DESC_NUMBERING" => "N",
                        "PAGER_DESC_NUMBERING_CACHE_TIME" => "3600000000",
                        "PAGER_SHOW_ALL" => "N",
                        "PAGER_SHOW_ALWAYS" => "N",
                        "PAGER_TEMPLATE" => ".default",
                        "PAGER_TITLE" => "Новости",
                        "PARENT_SECTION" => "",
                        "PARENT_SECTION_CODE" => "",
                        "PREVIEW_TRUNCATE_LEN" => "",
                        "PROPERTY_CODE" => array('LINK'),
                        "SET_BROWSER_TITLE" => "N",
                        "SET_LAST_MODIFIED" => "N",
                        "SET_META_DESCRIPTION" => "N",
                        "SET_META_KEYWORDS" => "N",
                        "SET_STATUS_404" => "N",
                        "SET_TITLE" => "N",
                        "SHOW_404" => "N",
                        "SORT_BY1" => "PROPERTY_SECTION",
                        "SORT_BY2" => "SORT",
                        "SORT_ORDER1" => "DESC,nulls",
                        "SORT_ORDER2" => "ASC",
                        "STRICT_SECTION_CHECK" => "N"
                    )
                );
            }

            return @ob_get_clean();
        },
        $result
    );

    echo $result;

}

if($request->get('ASYNC') === 'Y'){
    $APPLICATION->RestartBuffer();
    echo Json::encode(
        [
            'items' => $arResult['ITEMS'],
            'pagination' => $arResult['PAGINATION'],
            'sortList' => $arParams['SORT_LIST'],
            'sortSelected' => $arParams['SORT_SELECTED']
        ]
    );
    die();
}

if(($arParams['HIDE_SECTION_DESCRIPTION'] !== 'Y') && $arResult['UF_DESCRIPTION_TOP']){
    $description = '<div class="catalog__text">' . htmlspecialcharsback($arResult['UF_DESCRIPTION_TOP']) . '</div>';
    $APPLICATION->addViewContent('section-description-top', $description);
}