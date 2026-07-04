<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

use Bitrix\Catalog\ProductTable;
use Imedia\Main\Helpers\Catalog\Product;

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arResult['DETAIL_TEXT'] = strip_tags(htmlspecialchars_decode($arResult['DETAIL_TEXT']));

$arResult['MIN_DETAIL'] = '';
if (strlen($arResult['DETAIL_TEXT']) > 500) {
    $arResult['MIN_DETAIL'] = substr($arResult['DETAIL_TEXT'], 0, 500);
    $pos = strrpos($arResult['MIN_DETAIL'],'.');
    $arResult['MIN_DETAIL'] = substr($arResult['MIN_DETAIL'], 0, $pos) . '...';
}

if(ProductTable::TYPE_SKU === (int) $arResult['PRODUCT']['TYPE']){

    $data = [
        'id' => $arResult['ID'],
        'name' => $arResult['NAME'],
        'gallery' => [],
        'tree' => [],
        'offers' => [],
        'links' => [],
        'sizeTable' => $arResult['PROPERTIES']['SIZE_TABLE']['VALUE'],
        'type' => 'sku'
    ];

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

    $arResult['DATA'] = $data;

    if (
        defined('BX_COMP_MANAGED_CACHE')
        && is_object($GLOBALS['CACHE_MANAGER'])
    ){
        $cp =& $this->__component;
        if (strlen($cp->getCachePath())){
            $GLOBALS['CACHE_MANAGER']->RegisterTag('DATA');
        }
    }

}

$this->__component->SetResultCacheKeys(['DETAIL_PICTURE']);