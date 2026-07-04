<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var array $arParams
 * @var array $arResult
 * @var array $arCurSection
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Imedia\Main\Helpers\Catalog\Section;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

$this->setFrameMode(true);

$APPLICATION->SetPageProperty('classes--page', 'catalog');
$APPLICATION->SetPageProperty('classes--container', 'catalog__page');
$APPLICATION->SetPageProperty('title-classes', 'catalog__title');

$request = Context::getCurrent()->getRequest();
$queryList = $request->getQueryList()->toArray();
$isPagenPage = false;
foreach($queryList as $key => $value){

    if(strpos($key, 'PAGEN_') === 0){
        $isPagenPage = true;
        break;
    }
}

if (isset($arParams['USE_COMMON_SETTINGS_BASKET_POPUP']) && $arParams['USE_COMMON_SETTINGS_BASKET_POPUP'] == 'Y'){
    $basketAction = isset($arParams['COMMON_ADD_TO_BASKET_ACTION']) ?? $arParams['COMMON_ADD_TO_BASKET_ACTION'];
} else {
    $basketAction = isset($arParams['SECTION_ADD_TO_BASKET_ACTION']) ?? $arParams['SECTION_ADD_TO_BASKET_ACTION'];
}

$brandId = $APPLICATION->IncludeComponent(
    "bitrix:news.detail",
    "brand",
    Array(
        "DISPLAY_DATE" => $arParams["DISPLAY_DATE"],
        "DISPLAY_NAME" => $arParams["DISPLAY_NAME"],
        "DISPLAY_PICTURE" => 'Y',
        "DISPLAY_PREVIEW_TEXT" => $arParams["DISPLAY_PREVIEW_TEXT"],
        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "FIELD_CODE" => ['PREVIEW_PICTURE'],
        "PROPERTY_CODE" => $arParams["DETAIL_PROPERTY_CODE"],
        "DETAIL_URL" => '',
        "SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
        "META_KEYWORDS" => $arParams["META_KEYWORDS"],
        "META_DESCRIPTION" => $arParams["META_DESCRIPTION"],
        "BROWSER_TITLE" => $arParams["BROWSER_TITLE"],
        "SET_CANONICAL_URL" => $arParams["DETAIL_SET_CANONICAL_URL"],
        "DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
        "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
        "SET_TITLE" => $arParams["SET_TITLE"],
        "MESSAGE_404" => $arParams["MESSAGE_404"],
        "SET_STATUS_404" => $arParams["SET_STATUS_404"],
        "SHOW_404" => $arParams["SHOW_404"],
        "FILE_404" => $arParams["FILE_404"],
        "INCLUDE_IBLOCK_INTO_CHAIN" => 'N',
        "ADD_SECTIONS_CHAIN" => 'N',
        "ACTIVE_DATE_FORMAT" => $arParams["DETAIL_ACTIVE_DATE_FORMAT"],
        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
        "CACHE_TIME" => $arParams["CACHE_TIME"],
        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
        "USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
        "GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
        "DISPLAY_TOP_PAGER" => $arParams["DETAIL_DISPLAY_TOP_PAGER"],
        "DISPLAY_BOTTOM_PAGER" => $arParams["DETAIL_DISPLAY_BOTTOM_PAGER"],
        "PAGER_TITLE" => $arParams["DETAIL_PAGER_TITLE"],
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_TEMPLATE" => $arParams["DETAIL_PAGER_TEMPLATE"],
        "PAGER_SHOW_ALL" => $arParams["DETAIL_PAGER_SHOW_ALL"],
        "CHECK_DATES" => $arParams["CHECK_DATES"],
        "ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
        "ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
        "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
        "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
        "IBLOCK_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["news"],
        "USE_SHARE" => $arParams["USE_SHARE"],
        "SHARE_HIDE" => $arParams["SHARE_HIDE"],
        "SHARE_TEMPLATE" => $arParams["SHARE_TEMPLATE"],
        "SHARE_HANDLERS" => $arParams["SHARE_HANDLERS"],
        "SHARE_SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
        "SHARE_SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
        "ADD_ELEMENT_CHAIN" => 'Y',
        'STRICT_SECTION_CHECK' => (isset($arParams['STRICT_SECTION_CHECK']) ? $arParams['STRICT_SECTION_CHECK'] : ''),
        'CATALOG_SECTION_ID' => $arResult['VARIABLES']['CATALOG_SECTION_ID'],
        'IS_PAGEN_PAGE' => $isPagenPage
    ),
    $component
);

$isFilterPage = strpos($request->getRequestedPageDirectory(), '/filter/');
$arParams['USE_FILTER'] = (isset($arParams['USE_FILTER']) && $arParams['USE_FILTER'] == 'Y' ? 'Y' : 'N');
$isFilter = ($arParams['USE_FILTER'] == 'Y');

$arCurSection = Section::getCurrent([
    'SECTION_CODE' => $arResult['VARIABLES']['CATALOG_SECTION_CODE'],
    'SECTION_ID' => $arResult['VARIABLES']['CATALOG_SECTION_ID'],
], $arParams['CATALOG_IBLOCK_ID']);

$filterNameCatalogSectionBanner = 'arFilterCatalogSectionBanner';
$GLOBALS[$filterNameCatalogSectionBanner] = [
    'PROPERTY_BRAND' => $brandId
];

$GLOBALS[$filterNameCatalogSectionBanner]['PROPERTY_SECTION'] = ($arCurSection['ID']) ?: false;

$arSort = Section::getSort();
$arParams = array_merge($arParams, $arSort['PARAMS']);

ob_start();
$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "banner-catalog-top",
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
        "FILTER_NAME" => $filterNameCatalogSectionBanner,
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "IBLOCK_ID" => IblockHelper::getId('BANNER_CATALOG_TOP'),
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
        "PROPERTY_CODE" => array('LINK', 'LINK_TITLE', 'TITLE', 'SUBTITLE'),
        "SET_BROWSER_TITLE" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SORT_BY1" => "SORT",
        "SORT_BY2" => "ACTIVE_FROM",
        "SORT_ORDER1" => "ASC",
        "SORT_ORDER2" => "DESC",
        "STRICT_SECTION_CHECK" => "N"
    )
);
$APPLICATION->addViewContent('banner-catalog-top', ob_get_clean());
?>
<div class="catalog__inner">
    <div class="aside-filters">
        <?php
        $APPLICATION->IncludeComponent(
            'imedia:brands.menu.aside',
            '.default',
            [
                'SELECTED_SECTION_ID' => $arCurSection['ID'],
                'BRAND_ID' => $brandId,
                'IBLOCK_ID' => $arParams['CATALOG_IBLOCK_ID'],
                'SEF_FOLDER' => $arResult['FOLDER'] . $arResult['VARIABLES']['ELEMENT_CODE'] . '/'
            ],
            false,
            ['HIDE_ICONS' => true]
        )
        ?>
        <?php
        $APPLICATION->IncludeComponent(
            "bitrix:news.list",
            "banner-catalog-aside",
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
                "FILTER_NAME" => $filterNameCatalogSectionBanner,
                "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                "IBLOCK_ID" => IblockHelper::getId('BANNER_CATALOG_ASIDE'),
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
                "SORT_BY1" => "SORT",
                "SORT_BY2" => "ACTIVE_FROM",
                "SORT_ORDER1" => "ASC",
                "SORT_ORDER2" => "DESC",
                "STRICT_SECTION_CHECK" => "N"
            )
        );
        ?>
    </div>
    <div class="catalog__body">
        <?php
        $preFilterName = 'arPreFilter';
        $GLOBALS[$preFilterName] = [
            '=PROPERTY_BRAND.CODE' => $arResult['VARIABLES']['ELEMENT_CODE']
        ];

        $smartFilterUrlTemplate = ($arCurSection)
            ? $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["smart_filter"]
            : $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["smart_filter_root"];

        $smartFilterSefRule = str_replace(
            ['#ELEMENT_CODE#', '#CATALOG_SECTION_CODE_PATH#', '#CATALOG_SECTION_CODE#'],
            [
                $arResult['VARIABLES']['ELEMENT_CODE'],
                $arResult['VARIABLES']['CATALOG_SECTION_CODE_PATH'],
                $arResult['VARIABLES']['CATALOG_SECTION_CODE']
            ],
            $smartFilterUrlTemplate
        );

        ?>
        <div class="catalog__filters">
            <?php $APPLICATION->IncludeComponent(
                "imedia:catalog.smart.filter",
                "",
                array(
                    "IBLOCK_TYPE" => $arParams["CATALOG_IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["CATALOG_IBLOCK_ID"],
                    "SECTION_ID" => $arCurSection['ID'],
                    "FILTER_NAME" => $arParams["FILTER_NAME"],
                    "PRICE_CODE" => $arParams["~PRICE_CODE"],
                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                    "SAVE_IN_SESSION" => "N",
                    "FILTER_VIEW_MODE" => $arParams["FILTER_VIEW_MODE"],
                    "XML_EXPORT" => "N",
                    "SECTION_TITLE" => "NAME",
                    "SECTION_DESCRIPTION" => "DESCRIPTION",
                    'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                    "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
                    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                    'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                    "SEF_MODE" => $arParams["SEF_MODE"],
                    "SEF_RULE" => $smartFilterSefRule,
                    "SMART_FILTER_PATH" => $arResult["VARIABLES"]["SMART_FILTER_PATH"],
                    "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
                    "INSTANT_RELOAD" => $arParams["INSTANT_RELOAD"],
                    'PREFILTER_NAME' => $preFilterName,
                    'HIDDEN_PROPERTIES' => ['BRAND']
                ),
                $component,
                array('HIDE_ICONS' => 'Y')
            );?>
            <div class="catalog__view">
                <button class="catalog__view-btn grid-btn catalog__view-btn--active" type="button">
                    <svg viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.5" y="0.5" width="7" height="7" rx="0.5" stroke="#C0C0C0"></rect>
                        <rect x="11.5" y="0.5" width="7" height="7" rx="0.5" stroke="#C0C0C0"></rect>
                        <rect x="11.5" y="11.5" width="7" height="7" rx="0.5" stroke="#C0C0C0"></rect>
                        <rect x="0.5" y="11.5" width="7" height="7" rx="0.5" stroke="#C0C0C0"></rect>
                    </svg>
                </button>
                <button class="catalog__view-btn col-btn" type="button">
                    <svg viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.5" y="0.5" width="7" height="18" rx="0.5" stroke="#C0C0C0"></rect>
                        <rect x="11.5" y="0.5" width="7" height="18" rx="0.5" stroke="#C0C0C0"></rect>
                    </svg>
                </button>
                <button class="catalog__view-btn row-btn" type="button">
                    <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.5" y="0.5" width="17" height="6.57895" rx="0.5" stroke="#C0C0C0"></rect>
                        <rect x="0.5" y="10.9214" width="17" height="6.57895" rx="0.5" stroke="#C0C0C0"></rect>
                    </svg>
                </button>
            </div>
            <smart-filter-open class="filters-btn" type="button" aria-label="<?=Loc::getMessage('CATALOG_FILTERS')?>">
                <span class="filters-btn__icon">
                    <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_704_23020)">
                            <path d="M7.53077 16H5.89474V7.9276L0 1.26093V0H16V1.25373L10.386 7.9204V13.2876L7.53077 16ZM7.01754 14.9333H7.06572L9.26316 12.8458V7.54627L14.7196 1.06667H1.29474L7.01754 7.53907V14.9333Z" fill="black"></path>
                        </g>
                        <defs>
                            <clipPath id="clip0_704_23020">
                                <rect width="16" height="16" fill="white"></rect>
                            </clipPath>
                        </defs>
                    </svg>
                </span>
                <span><?=Loc::getMessage('CATALOG_FILTERS')?></span>
            </smart-filter-open>
        </div>
        <?php $APPLICATION->IncludeComponent(
            "imedia:catalog.section",
            "",
            array(
                "IBLOCK_TYPE" => $arParams["CATALOG_IBLOCK_TYPE"],
                "IBLOCK_ID" => $arParams["CATALOG_IBLOCK_ID"],
                "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
                "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
                "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
                "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
                "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"] ?? [],
                "PROPERTY_CODE_MOBILE" => $arParams["LIST_PROPERTY_CODE_MOBILE"],
                "INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
                "BASKET_URL" => $arParams["BASKET_URL"],
                "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                "FILTER_NAME" => $arParams["FILTER_NAME"],
                "CACHE_TYPE" => ($isFilterPage) ? 'N' : $arParams["CACHE_TYPE"],
                "CACHE_TIME" => $arParams["CACHE_TIME"],
                "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                "PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
                "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
                "PRICE_CODE" => $arParams["~PRICE_CODE"],
                "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],

                "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                "USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
                "ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
                "PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
                "PRODUCT_PROPERTIES" => (isset($arParams["PRODUCT_PROPERTIES"]) ? $arParams["PRODUCT_PROPERTIES"] : []),

                "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
                "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
                "PAGER_TITLE" => $arParams["PAGER_TITLE"],
                "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
                "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
                "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
                "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
                "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
                "PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
                "PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
                "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
                "LAZY_LOAD" => $arParams["LAZY_LOAD"],
                "MESS_BTN_LAZY_LOAD" => $arParams["~MESS_BTN_LAZY_LOAD"],
                "LOAD_ON_SCROLL" => $arParams["LOAD_ON_SCROLL"],

                "OFFERS_CART_PROPERTIES" => (isset($arParams["OFFERS_CART_PROPERTIES"]) ? $arParams["OFFERS_CART_PROPERTIES"] : []),
                "OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
                "OFFERS_PROPERTY_CODE" => (isset($arParams["LIST_OFFERS_PROPERTY_CODE"]) ? $arParams["LIST_OFFERS_PROPERTY_CODE"] : []),
                "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
                "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
                "OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
                "OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
                "OFFERS_LIMIT" => (isset($arParams["LIST_OFFERS_LIMIT"]) ? $arParams["LIST_OFFERS_LIMIT"] : 0),

                "SECTION_ID" => $arResult["VARIABLES"]["CATALOG_SECTION_ID"],
                "SECTION_CODE" => $arResult["VARIABLES"]["CATALOG_SECTION_CODE"],
                "SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
                "DETAIL_URL" => '',
                "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],

                'LABEL_PROP' => $arParams['LABEL_PROP'],
                'LABEL_PROP_MOBILE' => $arParams['LABEL_PROP_MOBILE'],
                'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],
                'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
                'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
                'PRODUCT_BLOCKS_ORDER' => $arParams['LIST_PRODUCT_BLOCKS_ORDER'],
                'PRODUCT_ROW_VARIANTS' => $arParams['LIST_PRODUCT_ROW_VARIANTS'],
                'ENLARGE_PRODUCT' => $arParams['LIST_ENLARGE_PRODUCT'],
                'ENLARGE_PROP' => isset($arParams['LIST_ENLARGE_PROP']) ? $arParams['LIST_ENLARGE_PROP'] : '',
                'SHOW_SLIDER' => $arParams['LIST_SHOW_SLIDER'],
                'SLIDER_INTERVAL' => isset($arParams['LIST_SLIDER_INTERVAL']) ? $arParams['LIST_SLIDER_INTERVAL'] : '',
                'SLIDER_PROGRESS' => isset($arParams['LIST_SLIDER_PROGRESS']) ? $arParams['LIST_SLIDER_PROGRESS'] : '',

                'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
                'OFFER_TREE_PROPS' => (isset($arParams['OFFER_TREE_PROPS']) ? $arParams['OFFER_TREE_PROPS'] : []),
                'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
                'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
                'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
                'MESS_SHOW_MAX_QUANTITY' => (isset($arParams['~MESS_SHOW_MAX_QUANTITY']) ? $arParams['~MESS_SHOW_MAX_QUANTITY'] : ''),
                'RELATIVE_QUANTITY_FACTOR' => (isset($arParams['RELATIVE_QUANTITY_FACTOR']) ? $arParams['RELATIVE_QUANTITY_FACTOR'] : ''),
                'MESS_RELATIVE_QUANTITY_MANY' => (isset($arParams['~MESS_RELATIVE_QUANTITY_MANY']) ? $arParams['~MESS_RELATIVE_QUANTITY_MANY'] : ''),
                'MESS_RELATIVE_QUANTITY_FEW' => (isset($arParams['~MESS_RELATIVE_QUANTITY_FEW']) ? $arParams['~MESS_RELATIVE_QUANTITY_FEW'] : ''),
                'MESS_BTN_BUY' => (isset($arParams['~MESS_BTN_BUY']) ? $arParams['~MESS_BTN_BUY'] : ''),
                'MESS_BTN_ADD_TO_BASKET' => (isset($arParams['~MESS_BTN_ADD_TO_BASKET']) ? $arParams['~MESS_BTN_ADD_TO_BASKET'] : ''),
                'MESS_BTN_SUBSCRIBE' => (isset($arParams['~MESS_BTN_SUBSCRIBE']) ? $arParams['~MESS_BTN_SUBSCRIBE'] : ''),
                'MESS_BTN_DETAIL' => (isset($arParams['~MESS_BTN_DETAIL']) ? $arParams['~MESS_BTN_DETAIL'] : ''),
                'MESS_NOT_AVAILABLE' => (isset($arParams['~MESS_NOT_AVAILABLE']) ? $arParams['~MESS_NOT_AVAILABLE'] : ''),
                'MESS_BTN_COMPARE' => (isset($arParams['~MESS_BTN_COMPARE']) ? $arParams['~MESS_BTN_COMPARE'] : ''),

                'USE_ENHANCED_ECOMMERCE' => (isset($arParams['USE_ENHANCED_ECOMMERCE']) ? $arParams['USE_ENHANCED_ECOMMERCE'] : ''),
                'DATA_LAYER_NAME' => (isset($arParams['DATA_LAYER_NAME']) ? $arParams['DATA_LAYER_NAME'] : ''),
                'BRAND_PROPERTY' => (isset($arParams['BRAND_PROPERTY']) ? $arParams['BRAND_PROPERTY'] : ''),

                'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
                "ADD_SECTIONS_CHAIN" => "N",
                'ADD_TO_BASKET_ACTION' => '',
                'SHOW_CLOSE_POPUP' => isset($arParams['COMMON_SHOW_CLOSE_POPUP']) ? $arParams['COMMON_SHOW_CLOSE_POPUP'] : '',
                'COMPARE_PATH' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['compare'],
                'COMPARE_NAME' => $arParams['COMPARE_NAME'],
                'USE_COMPARE_LIST' => 'Y',
                'BACKGROUND_IMAGE' => (isset($arParams['SECTION_BACKGROUND_IMAGE']) ? $arParams['SECTION_BACKGROUND_IMAGE'] : ''),
                'COMPATIBLE_MODE' => (isset($arParams['COMPATIBLE_MODE']) ? $arParams['COMPATIBLE_MODE'] : ''),
                'DISABLE_INIT_JS_IN_COMPONENT' => (isset($arParams['DISABLE_INIT_JS_IN_COMPONENT']) ? $arParams['DISABLE_INIT_JS_IN_COMPONENT'] : ''),

                'DISCOUNT_OFFERS_PRIORITY' => $arParams['DISCOUNT_OFFERS_PRIORITY'],

                "META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
                "META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
                "BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
                "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
                "SET_TITLE" => $arParams["SET_TITLE"],
                "MESSAGE_404" => $arParams["~MESSAGE_404"],
                "SET_STATUS_404" => $arParams["SET_STATUS_404"],
                "SHOW_404" => $arParams["SHOW_404"],
                "FILE_404" => $arParams["FILE_404"],
                'HIDE_SECTION_DESCRIPTION' => 'Y',
                'SORT_LIST' => $arSort['LIST'],
                'SORT_SELECTED' => $arSort['SELECTED'],

                'FILTER_NAME_CATALOG_SECTION' => $filterNameCatalogSectionBanner
            ),
            $component
        );?>
    </div>
</div>