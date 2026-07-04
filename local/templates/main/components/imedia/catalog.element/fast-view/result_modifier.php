<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Catalog\ProductTable;
use Imedia\Main\Helpers\Catalog\Product;

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$data = [
    'id' => $arResult['ID'],
    'name' => $arResult['NAME'],
    'gallery' => [],
    'tree' => [],
    'offers' => [],
    'links' => [],
    'sizeTable' => $arResult['PROPERTIES']['SIZE_TABLE']['VALUE']
];

switch((int) $arResult['PRODUCT']['TYPE']){
    case ProductTable::TYPE_SKU:
        $type = 'sku';
        break;
    default:
        $type = 'simple';
        break;
}

$data['type'] = $type;

foreach($arResult['GALLERY'] as $arItem){

    if(!$arItem['RESIZE']){
        continue;
    }

    $data['gallery'][] = [
        'id' => $arItem['ID'],
        'src' => $arItem['RESIZE'][0]['SIZES']['DEFAULT'],
        'src2x' => $arItem['RESIZE'][0]['SIZES']['DEFAULT_2X'],
        'width' => $arItem['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'],
        'height' => $arItem['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'],
        'alt' => $arItem['RESIZE'][0]['META']['ALT']
    ];
}

$mainProperties = Option::get('imedia.main', 'product_main_properties');
$arParams['PRODUCT_MAIN_PROPERTIES'] = $mainProperties ? explode(',', $mainProperties) : [];

if(empty($arResult['OFFERS'])){

    $data['offers'][] = Product::getOfferData($arResult, $arResult, $arParams);

} else {

    foreach($arResult['SKU_PROPS'] as $arProperty) {

        $offerCode = 'PROP_' . $arProperty['ID'];

        $tree = [
            'id' => $arProperty['ID'],
            'code' => $arProperty['CODE'],
            'name' => $arProperty['NAME'],
            'offerCode' => $offerCode,
            'values' => []
        ];

        foreach($arProperty['VALUES'] as $arValue){

            if((int) $arValue['ID'] === 0){

                $addEmptyValue = false;
                foreach($arResult['OFFERS'] as $arOffer){

                    if(
                        !isset($arOffer['TREE'][$offerCode]) ||
                        ($arOffer['TREE'][$offerCode] === 0)
                    ){
                        $addEmptyValue = true;
                        break;
                    }

                }

                if(!$addEmptyValue){
                    continue;
                }

            }

            $property = [
                'id' => $arValue['ID'],
                'name' => $arValue['NAME'],
                'picture' => $arValue['PICT'],
                'sort' => $arValue['SORT']
            ];

            $tree['values'][] = $property;
        }

        if(!empty($tree['values'])){
            $data['tree'][] = $tree;
        }
    }

    foreach($arResult['OFFERS'] as $arOffer){
        $data['offers'][] = Product::getOfferData($arOffer, $arResult, $arParams);
    }

}

if (!empty($arResult['BRAND'])){

    $linkSectionBrand = str_replace(
        '/catalog/',
        $arResult['BRAND']['DETAIL_PAGE_URL'],
        $arResult['SECTION']['SECTION_PAGE_URL']
    );

    $brandPicture = ($arResult['BRAND']['PICTURE']) ? [
        'src' => $arResult['BRAND']['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT'],
        'src2x' => $arResult['BRAND']['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X'],
        'width' => $arResult['BRAND']['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'],
        'height' => $arResult['BRAND']['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'],
        'alt' => $arResult['BRAND']['RESIZE'][0]['META']['ALT'] ?: $arResult['BRAND']['NAME']
    ] : null;

    $data['links'][] = [
        'id' => 'brand-section',
        'link' => $linkSectionBrand,
        'picture' => $brandPicture,
        'title' => Loc::getMessage('CT_BCE_CATALOG_ALL') . ' ' . mb_strtolower($arResult['SECTION']['NAME']) . ' ' . mb_strtolower($arResult['BRAND']['NAME']),
        'subtitle' => Loc::getMessage('CT_BCE_CATALOG_SECTION_BRAND')
    ];

    $data['links'][] = [
        'id' => 'brand',
        'link' => $arResult['BRAND']['DETAIL_PAGE_URL'],
        'picture' => $brandPicture,
        'title' => Loc::getMessage('CT_BCE_CATALOG_ALL_PRODUCTS') . ' ' . mb_strtolower($arResult['BRAND']['NAME']),
        'subtitle' => Loc::getMessage('CT_BCE_CATALOG_BRAND')
    ];

}

$data['links'][] = [
    'id' => 'section',
    'link' => $arResult['SECTION']['SECTION_PAGE_URL'],
    'picture' => ($arResult['SECTION']['PICTURE']) ? [
        'src' => $arResult['SECTION']['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT'],
        'src2x' => $arResult['SECTION']['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X'],
        'width' => $arResult['SECTION']['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'],
        'height' => $arResult['SECTION']['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'],
        'alt' => $arResult['SECTION']['PICTURE']['RESIZE'][0]['META']['ALT'] ?: $arResult['SECTION']['NAME']
    ] : null,
    'title' => Loc::getMessage('CT_BCE_CATALOG_ALL') . ' ' . mb_strtolower($arResult['SECTION']['NAME']),
    'subtitle' => Loc::getMessage('CT_BCE_CATALOG_SECTION')
];

$data['links'][] = [
    'id' => 'certificate',
    'link' => 'javascript:;',
    'picture' => [
        'src' => SITE_TEMPLATE_PATH . '/assets/images/icons/certificate.svg',
        'src2x' => SITE_TEMPLATE_PATH . '/assets/images/icons/certificate.svg',
        'width' => 40,
        'height' => 40,
        'alt' => 'certificate'
    ],
    'title' => Loc::getMessage('CT_BCE_CATALOG_ORIGINAL_BRANDS'),
    'subtitle' => Loc::getMessage('CT_BCE_CATALOG_WARRANTY')
];

$arResult = [
    'DATA' => $data
];

if (
    defined('BX_COMP_MANAGED_CACHE')
    && is_object($GLOBALS['CACHE_MANAGER'])
){
    $cp =& $this->__component;
    if (strlen($cp->getCachePath())){
        $GLOBALS['CACHE_MANAGER']->RegisterTag('DATA');
    }
}