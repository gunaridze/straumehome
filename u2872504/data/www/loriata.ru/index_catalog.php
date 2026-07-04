<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)	die();

use Imedia\Main\Helpers\Catalog\Selected;
use Imedia\Main\Helpers\Iblock\Facet;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Catalog\Property;

global $APPLICATION;

$APPLICATION->SetPageProperty('classes--page', 'home-page');
$APPLICATION->SetPageProperty('title-type', 'seo');

$selectedCatalogSectionId = Selected::get();
$catalogSectionList = Selected::getList();
$selectedCatalogSection = [];
foreach($catalogSectionList as $arSection){
    if((int) $arSection['ID'] === $selectedCatalogSectionId){
        $selectedCatalogSection = $arSection;
    }
}
?>
<section class="promo-sale">
    <div class="container">
        <div class="promo-sale__grid">
            <?php
            $filterNameBannerMainpageSlider = 'arFilterBannerMainpageSlider';
            $GLOBALS[$filterNameBannerMainpageSlider] = [
                'SECTION_ID' => $selectedCatalogSection['ID']
            ];

            $APPLICATION->IncludeComponent(
                "bitrix:news.list",
                "banner-mainpage-slider",
                array(
                    "ACTIVE_DATE_FORMAT" => "d.m.y",
                    "ADD_SECTIONS_CHAIN" => "N",
                    "AJAX_MODE" => "N",
                    "AJAX_OPTION_ADDITIONAL" => "",
                    "AJAX_OPTION_HISTORY" => "N",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "CACHE_FILTER" => "Y",
                    "CACHE_GROUPS" => "N",
                    "CACHE_TIME" => "86400",
                    "CACHE_TYPE" => "A",
                    "CHECK_DATES" => "Y",
                    "DETAIL_URL" => "",
                    "DISPLAY_BOTTOM_PAGER" => "N",
                    "DISPLAY_DATE" => "Y",
                    "DISPLAY_NAME" => "N",
                    "DISPLAY_PICTURE" => "Y",
                    "DISPLAY_PREVIEW_TEXT" => "Y",
                    "DISPLAY_TOP_PAGER" => "N",
                    "FIELD_CODE" => array(
                        0 => "PREVIEW_PICTURE"
                    ),
                    "FILTER_NAME" => $filterNameBannerMainpageSlider,
                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                    "IBLOCK_ID" => IblockHelper::getId('BANNER_MAINPAGE_SLIDER'),
                    "IBLOCK_TYPE" => 'content',
                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                    "INCLUDE_SUBSECTIONS" => "N",
                    "MESSAGE_404" => "",
                    "NEWS_COUNT" => "10",
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
                    "PROPERTY_CODE" => ['TITLE', 'SUBTITLE', 'LABEL', 'LINK'],
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
                ),
                false
            );

            $filterNameBannerMainpageTop = 'arFilterBannerMainpageTop';
            $GLOBALS[$filterNameBannerMainpageTop] = [
                'SECTION_ID' => $selectedCatalogSection['ID']
            ];

            $APPLICATION->IncludeComponent(
                "bitrix:news.list",
                "banner-mainpage-top",
                array(
                    "ACTIVE_DATE_FORMAT" => "d.m.y",
                    "ADD_SECTIONS_CHAIN" => "N",
                    "AJAX_MODE" => "N",
                    "AJAX_OPTION_ADDITIONAL" => "",
                    "AJAX_OPTION_HISTORY" => "N",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "CACHE_FILTER" => "Y",
                    "CACHE_GROUPS" => "N",
                    "CACHE_TIME" => "86400",
                    "CACHE_TYPE" => "A",
                    "CHECK_DATES" => "Y",
                    "DETAIL_URL" => "",
                    "DISPLAY_BOTTOM_PAGER" => "N",
                    "DISPLAY_DATE" => "Y",
                    "DISPLAY_NAME" => "N",
                    "DISPLAY_PICTURE" => "Y",
                    "DISPLAY_PREVIEW_TEXT" => "Y",
                    "DISPLAY_TOP_PAGER" => "N",
                    "FIELD_CODE" => array(
                        0 => "PREVIEW_PICTURE"
                    ),
                    "FILTER_NAME" => $filterNameBannerMainpageTop,
                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                    "IBLOCK_ID" => IblockHelper::getId('BANNER_MAINPAGE_TOP'),
                    "IBLOCK_TYPE" => 'content',
                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                    "INCLUDE_SUBSECTIONS" => "N",
                    "MESSAGE_404" => "",
                    "NEWS_COUNT" => "10",
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
                    "PROPERTY_CODE" => ['TITLE', 'SUBTITLE', 'LABEL', 'LINK'],
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
                ),
                false
            );
            ?>
        </div>
    </div>
</section>
<?php
$APPLICATION->IncludeComponent(
    'imedia:catalog.sections.top',
    '',
    [
        'SELECTED_CATALOG' => $selectedCatalogSectionId,
        'COUNT' => 10
    ],
    false,
    ['HIDE_ICONS' => true]
);

$filterNameProductsPopular = 'arFilterProductsPopular';
$GLOBALS[$filterNameProductsPopular] = [
    '!PROPERTY_' . Property::getCode('HIT') => false
];

$APPLICATION->IncludeComponent(
    "imedia:catalog.section",
    "slider",
    array(
        "ACTION_VARIABLE" => "action",
        "ADD_PICT_PROP" => "-",
        "ADD_PROPERTIES_TO_BASKET" => "N",
        "ADD_SECTIONS_CHAIN" => "N",
        "ADD_TO_BASKET_ACTION" => "ADD",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "N",
        "BACKGROUND_IMAGE" => "-",
        "BASKET_URL" => "/personal/basket.php",
        "BROWSER_TITLE" => "-",
        "CACHE_FILTER" => "Y",
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "N",
        "COMPATIBLE_MODE" => "N",
        "CONVERT_CURRENCY" => "N",
        "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[{\"CLASS_ID\":\"CondIBProp:4:7\",\"DATA\":{\"logic\":\"Equal\",\"value\":1}}]}",
        "DETAIL_URL" => "",
        "DISABLE_INIT_JS_IN_COMPONENT" => "N",
        "DISPLAY_BOTTOM_PAGER" => "N",
        "DISPLAY_COMPARE" => "N",
        "DISPLAY_TOP_PAGER" => "N",
        "ELEMENT_SORT_FIELD" => "RAND",
        "ELEMENT_SORT_FIELD2" => "id",
        "ELEMENT_SORT_ORDER" => "asc",
        "ELEMENT_SORT_ORDER2" => "desc",
        "ENLARGE_PRODUCT" => "STRICT",
        "FILTER_NAME" => $filterNameProductsPopular,
        "HIDE_NOT_AVAILABLE" => "Y",
        "HIDE_NOT_AVAILABLE_OFFERS" => "Y",
        "IBLOCK_ID" => IblockHelper::getId('CATALOG'),
        "IBLOCK_TYPE" => 'catalog',
        "INCLUDE_SUBSECTIONS" => "Y",
        "LABEL_PROP" => Property::getLabels(),
        "LAZY_LOAD" => "N",
        "LINE_ELEMENT_COUNT" => "3",
        "LOAD_ON_SCROLL" => "N",
        "MESSAGE_404" => "",
        "MESS_BTN_ADD_TO_BASKET" => "В корзину",
        "MESS_BTN_BUY" => "Купить",
        "MESS_BTN_DETAIL" => "Подробнее",
        "MESS_BTN_SUBSCRIBE" => "Подписаться",
        "MESS_NOT_AVAILABLE" => "Нет в наличии",
        "META_DESCRIPTION" => "-",
        "META_KEYWORDS" => "-",
        "OFFERS_FIELD_CODE" => ['NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL'],
        "OFFERS_LIMIT" => "0",
        "PAGER_BASE_LINK_ENABLE" => "N",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "N",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_TEMPLATE" => ".default",
        "PAGER_TITLE" => "Товары",
        "PAGE_ELEMENT_COUNT" => "20",
        "PARTIAL_PRODUCT_PROPERTIES" => "N",
        "PRICE_CODE" => Property::getPrice(),
        "PRICE_VAT_INCLUDE" => "Y",
        "PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
        "PRODUCT_DISPLAY_MODE" => "Y",
        "PRODUCT_ID_VARIABLE" => "id",
        "PRODUCT_PROPERTIES" => "",
        "PRODUCT_PROPS_VARIABLE" => "prop",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity",
        "PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true}]",
        "PRODUCT_SUBSCRIPTION" => "N",
        "PROPERTY_CODE" => array(),
        "PROPERTY_CODE_MOBILE" => "",
        "RCM_PROD_ID" => "",
        "RCM_TYPE" => "personal",
        "SECTION_CODE" => "",
        "SECTION_ID" => $selectedCatalogSection['ID'],
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "SECTION_URL" => "",
        "SECTION_USER_FIELDS" => array(),
        "SEF_MODE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SHOW_ALL_WO_SECTION" => "Y",
        "SHOW_CLOSE_POPUP" => "N",
        "SHOW_DISCOUNT_PERCENT" => "N",
        "SHOW_FROM_SECTION" => "N",
        "SHOW_MAX_QUANTITY" => "N",
        "SHOW_OLD_PRICE" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "SHOW_SLIDER" => "N",
        "SLIDER_INTERVAL" => "3000",
        "SLIDER_PROGRESS" => "N",
        "TEMPLATE_THEME" => "blue",
        "USE_ENHANCED_ECOMMERCE" => "N",
        "USE_MAIN_ELEMENT_SECTION" => "N",
        "USE_PRICE_COUNT" => "N",
        "USE_PRODUCT_QUANTITY" => "N",
        "COMPONENT_TEMPLATE" => "slider",
        "COMPOSITE_FRAME_MODE" => "A",
        "COMPOSITE_FRAME_TYPE" => "AUTO",
        'SLIDER_TITLE' => 'Популярные товары',
        'SLIDER_ID' => 'popular-goods-slider'
    ),
    false
);

$filterNameBannerActual = 'arFilterBannerActual';
$GLOBALS[$filterNameBannerActual] = [
    'SECTION_ID' => $selectedCatalogSection['ID']
];

$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "banner-actual",
    array(
        "ACTIVE_DATE_FORMAT" => "d.m.y",
        "ADD_SECTIONS_CHAIN" => "N",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "CACHE_FILTER" => "Y",
        "CACHE_GROUPS" => "N",
        "CACHE_TIME" => "86400",
        "CACHE_TYPE" => "A",
        "CHECK_DATES" => "Y",
        "DETAIL_URL" => "",
        "DISPLAY_BOTTOM_PAGER" => "N",
        "DISPLAY_DATE" => "Y",
        "DISPLAY_NAME" => "N",
        "DISPLAY_PICTURE" => "Y",
        "DISPLAY_PREVIEW_TEXT" => "Y",
        "DISPLAY_TOP_PAGER" => "N",
        "FIELD_CODE" => array(
            0 => "PREVIEW_PICTURE"
        ),
        "FILTER_NAME" => $filterNameBannerActual,
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "IBLOCK_ID" => IblockHelper::getId('BANNER_ACTUAL'),
        "IBLOCK_TYPE" => 'content',
        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
        "INCLUDE_SUBSECTIONS" => "N",
        "MESSAGE_404" => "",
        "NEWS_COUNT" => "3",
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
        "PROPERTY_CODE" => ['TITLE', 'SUBTITLE', 'LABEL', 'LINK'],
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
    ),
    false
);

$brandsIds = Facet::getValuesFromPropertyInSection(
    IblockHelper::getId('CATALOG'),
    'BRAND',
    $selectedCatalogSection['ID']
);

if(!empty($brandsIds)){

    $filterNameBrands = 'arFilterBrands';
    $GLOBALS[$filterNameBrands] = [
        'ID' => $brandsIds,
        '!PREVIEW_PICTURE' => false,
        '!PROPERTY_MAINPAGE' => false
    ];

    $APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "brands-mainpage",
        array(
            "ACTIVE_DATE_FORMAT" => "d.m.y",
            "ADD_SECTIONS_CHAIN" => "N",
            "AJAX_MODE" => "N",
            "AJAX_OPTION_ADDITIONAL" => "",
            "AJAX_OPTION_HISTORY" => "N",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "CACHE_FILTER" => "Y",
            "CACHE_GROUPS" => "N",
            "CACHE_TIME" => "86400",
            "CACHE_TYPE" => "A",
            "CHECK_DATES" => "Y",
            "DETAIL_URL" => "",
            "DISPLAY_BOTTOM_PAGER" => "N",
            "DISPLAY_DATE" => "Y",
            "DISPLAY_NAME" => "N",
            "DISPLAY_PICTURE" => "Y",
            "DISPLAY_PREVIEW_TEXT" => "Y",
            "DISPLAY_TOP_PAGER" => "N",
            "FIELD_CODE" => array(
                0 => "PREVIEW_PICTURE",
            ),
            "FILTER_NAME" => $filterNameBrands,
            "HIDE_LINK_WHEN_NO_DETAIL" => "N",
            "IBLOCK_ID" => IblockHelper::getId('BRANDS'),
            "IBLOCK_TYPE" => 'content',
            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
            "INCLUDE_SUBSECTIONS" => "N",
            "MESSAGE_404" => "",
            "NEWS_COUNT" => "6",
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
            "PROPERTY_CODE" => [],
            "SET_BROWSER_TITLE" => "N",
            "SET_LAST_MODIFIED" => "N",
            "SET_META_DESCRIPTION" => "N",
            "SET_META_KEYWORDS" => "N",
            "SET_STATUS_404" => "N",
            "SET_TITLE" => "N",
            "SHOW_404" => "N",
            "SORT_BY1" => "RAND",
            "SORT_BY2" => "ID",
            "SORT_ORDER1" => "ASC",
            "SORT_ORDER2" => "DESC",
            "STRICT_SECTION_CHECK" => "N",
            'SELECTED_SECTION_PATH' => $selectedCatalogSection['CODE']
        ),
        false
    );
}

$filterNameProductsNew = 'arFilterProductsNew';
$GLOBALS[$filterNameProductsNew] = [
    '!PROPERTY_' . Property::getCode('NEW') => false
];

$APPLICATION->IncludeComponent(
    "imedia:catalog.section",
    "slider",
    array(
        "ACTION_VARIABLE" => "action",
        "ADD_PICT_PROP" => "-",
        "ADD_PROPERTIES_TO_BASKET" => "N",
        "ADD_SECTIONS_CHAIN" => "N",
        "ADD_TO_BASKET_ACTION" => "ADD",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "N",
        "BACKGROUND_IMAGE" => "-",
        "BASKET_URL" => "/personal/basket.php",
        "BROWSER_TITLE" => "-",
        "CACHE_FILTER" => "Y",
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "N",
        "COMPATIBLE_MODE" => "N",
        "CONVERT_CURRENCY" => "N",
        "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[{\"CLASS_ID\":\"CondIBProp:4:7\",\"DATA\":{\"logic\":\"Equal\",\"value\":1}}]}",
        "DETAIL_URL" => "",
        "DISABLE_INIT_JS_IN_COMPONENT" => "N",
        "DISPLAY_BOTTOM_PAGER" => "N",
        "DISPLAY_COMPARE" => "N",
        "DISPLAY_TOP_PAGER" => "N",
        "ELEMENT_SORT_FIELD" => "RAND",
        "ELEMENT_SORT_FIELD2" => "id",
        "ELEMENT_SORT_ORDER" => "asc",
        "ELEMENT_SORT_ORDER2" => "desc",
        "ENLARGE_PRODUCT" => "STRICT",
        "FILTER_NAME" => $filterNameProductsNew,
        "HIDE_NOT_AVAILABLE" => "Y",
        "HIDE_NOT_AVAILABLE_OFFERS" => "Y",
        "IBLOCK_ID" => IblockHelper::getId('CATALOG'),
        "IBLOCK_TYPE" => 'catalog',
        "INCLUDE_SUBSECTIONS" => "Y",
        "LABEL_PROP" => Property::getLabels(),
        "LAZY_LOAD" => "N",
        "LINE_ELEMENT_COUNT" => "3",
        "LOAD_ON_SCROLL" => "N",
        "MESSAGE_404" => "",
        "MESS_BTN_ADD_TO_BASKET" => "В корзину",
        "MESS_BTN_BUY" => "Купить",
        "MESS_BTN_DETAIL" => "Подробнее",
        "MESS_BTN_SUBSCRIBE" => "Подписаться",
        "MESS_NOT_AVAILABLE" => "Нет в наличии",
        "META_DESCRIPTION" => "-",
        "META_KEYWORDS" => "-",
        "OFFERS_FIELD_CODE" => ['NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL'],
        "OFFERS_LIMIT" => "0",
        "PAGER_BASE_LINK_ENABLE" => "N",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "N",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_TEMPLATE" => ".default",
        "PAGER_TITLE" => "Товары",
        "PAGE_ELEMENT_COUNT" => "20",
        "PARTIAL_PRODUCT_PROPERTIES" => "N",
        "PRICE_CODE" => Property::getPrice(),
        "PRICE_VAT_INCLUDE" => "Y",
        "PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
        "PRODUCT_DISPLAY_MODE" => "Y",
        "PRODUCT_ID_VARIABLE" => "id",
        "PRODUCT_PROPERTIES" => "",
        "PRODUCT_PROPS_VARIABLE" => "prop",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity",
        "PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true},{'VARIANT':'0','BIG_DATA':true}]",
        "PRODUCT_SUBSCRIPTION" => "N",
        "PROPERTY_CODE" => array(),
        "PROPERTY_CODE_MOBILE" => "",
        "RCM_PROD_ID" => "",
        "RCM_TYPE" => "personal",
        "SECTION_CODE" => "",
        "SECTION_ID" => $selectedCatalogSection['ID'],
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "SECTION_URL" => "",
        "SECTION_USER_FIELDS" => array(),
        "SEF_MODE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SHOW_ALL_WO_SECTION" => "Y",
        "SHOW_CLOSE_POPUP" => "N",
        "SHOW_DISCOUNT_PERCENT" => "N",
        "SHOW_FROM_SECTION" => "N",
        "SHOW_MAX_QUANTITY" => "N",
        "SHOW_OLD_PRICE" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "SHOW_SLIDER" => "N",
        "SLIDER_INTERVAL" => "3000",
        "SLIDER_PROGRESS" => "N",
        "TEMPLATE_THEME" => "blue",
        "USE_ENHANCED_ECOMMERCE" => "N",
        "USE_MAIN_ELEMENT_SECTION" => "N",
        "USE_PRICE_COUNT" => "N",
        "USE_PRODUCT_QUANTITY" => "N",
        "COMPONENT_TEMPLATE" => "slider",
        "COMPOSITE_FRAME_MODE" => "A",
        "COMPOSITE_FRAME_TYPE" => "AUTO",
        'SLIDER_TITLE' => 'Новинки',
        'SLIDER_ID' => 'catalog-new-slider'
    ),
    false
);

$filterNameBannerBottom = 'arFilterBannerBottom';
$GLOBALS[$filterNameBannerBottom] = [
    'SECTION_ID' => $selectedCatalogSection['ID']
];

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
        "CACHE_FILTER" => "Y",
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
        "FILTER_NAME" => $filterNameBannerBottom,
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "IBLOCK_ID" => IblockHelper::getId('BANNER_MAIN_BOTTOM'),
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

$filterNameBlog = 'arFilterBlog';
$GLOBALS[$filterNameBlog] = [
    '?TAGS' => strtolower($selectedCatalogSection['NAME'])
];

$APPLICATION->IncludeComponent(
	"bitrix:news.list", 
	"blog--mainpage", 
	array(
		"ACTIVE_DATE_FORMAT" => "d.m.y",
		"ADD_SECTIONS_CHAIN" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"CACHE_FILTER" => "Y",
		"CACHE_GROUPS" => "N",
		"CACHE_TIME" => "86400",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"DETAIL_URL" => "",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "N",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"FIELD_CODE" => array(
			0 => "TAGS",
			1 => "PREVIEW_PICTURE",
			2 => "",
		),
		"FILTER_NAME" => $filterNameBlog,
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => IblockHelper::getId("BLOG"),
		"IBLOCK_TYPE" => "content",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"INCLUDE_SUBSECTIONS" => "N",
		"MESSAGE_404" => "",
		"NEWS_COUNT" => "3",
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
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"SET_BROWSER_TITLE" => "N",
		"SET_LAST_MODIFIED" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "N",
		"SHOW_404" => "N",
		"SORT_BY1" => "SORT",
		"SORT_BY2" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_ORDER2" => "ASC",
		"STRICT_SECTION_CHECK" => "N",
		"SELECTED_SECTION_NAME" => $selectedCatalogSection["NAME"],
		"COMPONENT_TEMPLATE" => "blog--mainpage"
	),
	false
);

?>
<div class="section">
    <div class="container">
        <?php
        $APPLICATION->IncludeComponent(
            'bitrix:main.include',
            'catalog_index',
            [
                'AREA_FILE_SHOW' => 'page',
                'AREA_FILE_SUFFIX' => 'seo_text'
            ]
        );

        $APPLICATION->ShowViewContent('title');
        ?>
    </div>
</div>