<?php

namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Config\Option;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Iblock\Iblock;

class QuickViewProduct extends Controller
{
    public function configureActions(): array
    {
        return [
            'get' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET]
                    ),
                    new ActionFilter\Csrf(),
                ]
            ]
        ];
    }

    public function getAction(int $productId)
    {
        return new Response\Component(
            'imedia:catalog.element',
            'fast-view',
            [
                'ACTION_VARIABLE' => 'action',
                'ADD_DETAIL_TO_SLIDER' => 'N',
                'ADD_ELEMENT_CHAIN' => 'N',
                'ADD_PICT_PROP' => 'GALLERY',
                'ADD_PROPERTIES_TO_BASKET' => 'Y',
                'ADD_SECTIONS_CHAIN' => 'N',
                'ADD_TO_BASKET_ACTION' => ['BUY'],
                'ADD_TO_BASKET_ACTION_PRIMARY' => ['BUY'],
                'BACKGROUND_IMAGE' => '-',
                'BASKET_URL' => '/personal/basket.php',
                'BLOG_USE' => 'N',
                'BRAND_PROPERTY' => 'BRAND_REF',
                'BRAND_PROP_CODE' => ['BRAND_REF'],
                'BRAND_USE' => 'N',
                'BROWSER_TITLE' => '-',
                'CACHE_GROUPS' => 'Y',
                'CACHE_TIME' => '36000000',
                'CACHE_TYPE' => 'A',
                'CHECK_SECTION_ID_VARIABLE' => 'N',
                'COMPARE_PATH' => '',
                'COMPATIBLE_MODE' => 'N',
                'CONVERT_CURRENCY' => 'N',
                'DATA_LAYER_NAME' => 'dataLayer',
                'DETAIL_PICTURE_MODE' => ['POPUP', 'MAGNIFIER'],
                'DETAIL_URL' => '',
                'DISABLE_INIT_JS_IN_COMPONENT' => 'N',
                'DISCOUNT_PERCENT_POSITION' => 'bottom-right',
                'DISPLAY_COMPARE' => 'N',
                'DISPLAY_NAME' => 'Y',
                'DISPLAY_PREVIEW_TEXT_MODE' => 'E',
                'ELEMENT_CODE' => '',
                'ELEMENT_ID' => $productId,
                'FB_USE' => 'N',
                'FILE_404' => '',
                'GIFTS_DETAIL_BLOCK_TITLE' => 'Выберите один из подарков',
                'GIFTS_DETAIL_HIDE_BLOCK_TITLE' => 'N',
                'GIFTS_DETAIL_PAGE_ELEMENT_COUNT' => '3',
                'GIFTS_DETAIL_TEXT_LABEL_GIFT' => 'Подарок',
                'GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE' => 'Выберите один из товаров, чтобы получить подарок',
                'GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE' => 'N',
                'GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT' => '3',
                'GIFTS_MESS_BTN_BUY' => 'Выбрать',
                'GIFTS_SHOW_DISCOUNT_PERCENT' => 'Y',
                'GIFTS_SHOW_IMAGE' => 'Y',
                'GIFTS_SHOW_NAME' => 'Y',
                'GIFTS_SHOW_OLD_PRICE' => 'Y',
                'HIDE_NOT_AVAILABLE_OFFERS' => 'N',
                'IBLOCK_ID' => Iblock::getId('CATALOG'),
                'IBLOCK_TYPE' => 'catalog',
                'LABEL_PROP' => Property::getLabels(),
                'LABEL_PROP_MOBILE' => [],
                'LABEL_PROP_POSITION' => 'top-left',
                'LINK_ELEMENTS_URL' => 'link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#',
                'LINK_IBLOCK_ID' => '',
                'LINK_IBLOCK_TYPE' => '',
                'LINK_PROPERTY_SID' => '',
                'MAIN_BLOCK_OFFERS_PROPERTY_CODE' => [],
                'MAIN_BLOCK_PROPERTY_CODE' => [],
                'MESSAGE_404' => '',
                'MESS_BTN_ADD_TO_BASKET' => 'В корзину',
                'MESS_BTN_BUY' => 'Купить',
                'MESS_BTN_COMPARE' => 'Сравнить',
                'MESS_BTN_SUBSCRIBE' => 'Подписаться',
                'MESS_COMMENTS_TAB' => 'Комментарии',
                'MESS_DESCRIPTION_TAB' => 'Описание',
                'MESS_NOT_AVAILABLE' => 'Нет в наличии',
                'MESS_PROPERTIES_TAB' => 'Характеристики',
                'MESS_RELATIVE_QUANTITY_FEW' => 'мало',
                'MESS_RELATIVE_QUANTITY_MANY' => 'много',
                'MESS_SHOW_MAX_QUANTITY' => 'Наличие',
                'META_DESCRIPTION' => '-',
                'META_KEYWORDS' => '-',
                'OFFERS_CART_PROPERTIES' => [],
                'OFFERS_FIELD_CODE' => ['NAME', 'DETAIL_PAGE_URL'],
                'OFFERS_LIMIT' => '0',
                'OFFERS_PROPERTY_CODE' => [],
                'OFFERS_SORT_FIELD' => 'sort',
                'OFFERS_SORT_FIELD2' => 'id',
                'OFFERS_SORT_ORDER' => 'asc',
                'OFFERS_SORT_ORDER2' => 'desc',
                'OFFER_ADD_PICT_PROP' => 'GALLERY',
                'OFFER_TREE_PROPS' => ['SIZES'],
                'PARTIAL_PRODUCT_PROPERTIES' => 'N',
                'PRICE_CODE' => Property::getPrice(),
                'PRICE_VAT_INCLUDE' => 'Y',
                'PRICE_VAT_SHOW_VALUE' => 'N',
                'PRODUCT_ID_VARIABLE' => 'id',
                'PRODUCT_INFO_BLOCK_ORDER' => 'sku,props',
                'PRODUCT_PAY_BLOCK_ORDER' => 'rating,price,quantityLimit,quantity,buttons',
                'PRODUCT_PROPERTIES' => [],
                'PRODUCT_PROPS_VARIABLE' => 'prop',
                'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
                'PRODUCT_SUBSCRIPTION' => 'Y',
                'PROPERTY_CODE' => [],
                'RELATIVE_QUANTITY_FACTOR' => '5',
                'SECTION_CODE' => '',
                'SECTION_CODE_PATH' => '',
                'SECTION_ID' => '',
                'SECTION_ID_VARIABLE' => 'SECTION_ID',
                'SECTION_URL' => '',
                'SEF_MODE' => 'N',
                'SEF_RULE' => '',
                'SET_BROWSER_TITLE' => 'N',
                'SET_CANONICAL_URL' => 'N',
                'SET_LAST_MODIFIED' => 'N',
                'SET_META_DESCRIPTION' => 'N',
                'SET_META_KEYWORDS' => 'N',
                'SET_STATUS_404' => 'N',
                'SET_TITLE' => 'N',
                'SET_VIEWED_IN_COMPONENT' => 'N',
                'SHOW_404' => 'N',
                'SHOW_CLOSE_POPUP' => 'N',
                'SHOW_DEACTIVATED' => 'N',
                'SHOW_DISCOUNT_PERCENT' => 'Y',
                'SHOW_MAX_QUANTITY' => 'N',
                'SHOW_OLD_PRICE' => 'Y',
                'SHOW_PRICE_COUNT' => '1',
                'SHOW_SLIDER' => 'N',
                'SLIDER_INTERVAL' => '5000',
                'SLIDER_PROGRESS' => 'N',
                'STRICT_SECTION_CHECK' => 'N',
                'TEMPLATE_THEME' => 'blue',
                'USE_COMMENTS' => 'N',
                'USE_ELEMENT_COUNTER' => 'Y',
                'USE_ENHANCED_ECOMMERCE' => 'Y',
                'USE_GIFTS_DETAIL' => 'N',
                'USE_GIFTS_MAIN_PR_SECTION_LIST' => 'N',
                'USE_MAIN_ELEMENT_SECTION' => 'Y',
                'USE_PRICE_COUNT' => 'N',
                'USE_PRODUCT_QUANTITY' => 'Y',
                'USE_VOTE_RATING' => 'N',
                'VK_USE' => 'N',
                'VOTE_DISPLAY_AS_RATING' => 'rating'
            ]
        );
    }
}
