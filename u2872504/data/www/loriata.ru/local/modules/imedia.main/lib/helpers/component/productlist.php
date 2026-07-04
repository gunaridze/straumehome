<?php
namespace Imedia\Main\Helpers\Component;

use Bitrix\Catalog\PriceTable;
use Bitrix\Iblock;
use Bitrix\Catalog;
use Imedia\Main\Helpers\Catalog\Price as PriceHelper;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Formatter\Text;
use Imedia\Main\Helpers\Iblock\Brand;
use Imedia\Main\Helpers\Image\Resize;
use Imedia\Main\Helpers\Catalog\Price;

trait ProductList
{
    protected function modifyDisplayProperties($iblock, &$iblockElements){

        $productsIds = array_keys($iblockElements);
        if(empty($productsIds)){
            return;
        }

        $arPropertiesCode = [];

        foreach($this->arParams['LABEL_PROP'] as $propertyCode){
            $arPropertiesCode[] = $propertyCode;
        }

        $arPropertiesCode[] = Property::getCode('BRAND');
        $arPropertiesCode[] = Property::getCode('SIZE');

        $arProperties = [];
        \CIBlockElement::GetPropertyValuesArray(
            $arProperties,
            $this->arParams['IBLOCK_ID'],
            [
                'ID' => $productsIds,
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID']
            ],
            [
                'CODE' => $arPropertiesCode
            ]
        );

        foreach($iblockElements as $key => $arItem){
            $iblockElements[$key]['PROPERTIES'] = $arProperties[$arItem['ID']];
        }

    }

    protected function editTemplateProductPictures(&$item)
    {

    }

    protected function editTemplateJsOffers(&$item)
    {

    }

    protected function initIblockPropertyFeatures()
    {
        if (!Iblock\Model\PropertyFeature::isEnabledFeatures()){
            return;
        }

        foreach (array_keys($this->storage['IBLOCK_PARAMS']) as $iblockId){
            $this->loadOfferTreePropertyCodes($iblockId);
        }

        unset($iblockId);
    }

    protected function loadDisplayPropertyCodes($iblockId)
    {

    }

    protected function modifyElementCommonData(array &$element)
    {
        $element['ID'] = (int) $element['ID'];
        $element['IBLOCK_ID'] = (int) $element['IBLOCK_ID'];

        if ($this->arParams['HIDE_DETAIL_URL']){
            $element['DETAIL_PAGE_URL'] = $element['~DETAIL_PAGE_URL'] = '';
        }

        if ($this->isEnableCompatible()){
            $element['ACTIVE_FROM'] = (isset($element['DATE_ACTIVE_FROM']) ? $element['DATE_ACTIVE_FROM'] : null);
            $element['ACTIVE_TO'] = (isset($element['DATE_ACTIVE_TO']) ? $element['DATE_ACTIVE_TO'] : null);
        }

        Iblock\Component\Tools::getFieldImageData(
            $element,
            ['PREVIEW_PICTURE', 'DETAIL_PICTURE'],
            Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
            'IPROPERTY_VALUES'
        );

        if (isset($element['~TYPE'])){
            $productFields = $this->getProductFields($element['IBLOCK_ID']);
            $translateFields = $this->getCompatibleProductFields();

            $element['PRODUCT'] = [
                'TYPE' => (int)$element['~TYPE'],
                'AVAILABLE' => $element['~AVAILABLE'],
                'BUNDLE' => $element['~BUNDLE'],
                'QUANTITY' => $element['~QUANTITY'],
                'QUANTITY_TRACE' => $element['~QUANTITY_TRACE'],
                'CAN_BUY_ZERO' => $element['~CAN_BUY_ZERO'],
                'MEASURE' => (int)$element['~MEASURE'],
                'SUBSCRIBE' => $element['~SUBSCRIBE'],
                'VAT_ID' => (int)$element['~VAT_ID'],
                'VAT_RATE' => 0,
                'VAT_INCLUDED' => $element['~VAT_INCLUDED'],
                'WEIGHT' => (float)$element['~WEIGHT'],
                'WIDTH' => (float)$element['~WIDTH'],
                'LENGTH' => (float)$element['~LENGTH'],
                'HEIGHT' => (float)$element['~HEIGHT'],
                'PAYMENT_TYPE' => $element['~PAYMENT_TYPE'],
                'RECUR_SCHEME_TYPE' => $element['~RECUR_SCHEME_TYPE'],
                'RECUR_SCHEME_LENGTH' => (int)$element['~RECUR_SCHEME_LENGTH'],
                'TRIAL_PRICE_ID' => (int)$element['~TRIAL_PRICE_ID']
            ];

            $vatId = 0;
            $vatRate = 0;
            if ($element['PRODUCT']['VAT_ID'] > 0){
                $vatId = $element['PRODUCT']['VAT_ID'];
            }  elseif ($this->storage['IBLOCKS_VAT'][$element['IBLOCK_ID']] > 0){
                $vatId = $this->storage['IBLOCKS_VAT'][$element['IBLOCK_ID']];
            }

            if ($vatId > 0 && isset($this->storage['VATS'][$vatId])){
                $vatRate = $this->storage['VATS'][$vatId];
            }

            $element['PRODUCT']['VAT_RATE'] = $vatRate;
            unset($vatRate, $vatId);

            if ($this->isEnableCompatible()){
                foreach ($translateFields as $currentKey => $oldKey){
                    $element[$oldKey] = $element[$currentKey];
                }
                unset($currentKey, $oldKey);
                $element['~CATALOG_VAT'] = $element['PRODUCT']['VAT_RATE'];
                $element['CATALOG_VAT'] = $element['PRODUCT']['VAT_RATE'];
            } else {
                // temporary (compatibility custom templates)
                $element['~CATALOG_TYPE'] = $element['PRODUCT']['TYPE'];
                $element['CATALOG_TYPE'] = $element['PRODUCT']['TYPE'];
                $element['~CATALOG_QUANTITY'] = $element['PRODUCT']['QUANTITY'];
                $element['CATALOG_QUANTITY'] = $element['PRODUCT']['QUANTITY'];
                $element['~CATALOG_QUANTITY_TRACE'] = $element['PRODUCT']['QUANTITY_TRACE'];
                $element['CATALOG_QUANTITY_TRACE'] = $element['PRODUCT']['QUANTITY_TRACE'];
                $element['~CATALOG_CAN_BUY_ZERO'] = $element['PRODUCT']['CAN_BUY_ZERO'];
                $element['CATALOG_CAN_BUY_ZERO'] = $element['PRODUCT']['CAN_BUY_ZERO'];
                $element['~CATALOG_SUBSCRIBE'] = $element['PRODUCT']['SUBSCRIBE'];
                $element['CATALOG_SUBSCRIBE'] = $element['PRODUCT']['SUBSCRIBE'];
            }

            foreach ($productFields as $field)
                unset($element[$field], $element['~'.$field]);
            unset($field);
        } else {
            $element['PRODUCT'] = [
                'TYPE' => null,
                'AVAILABLE' => null
            ];
        }

        $element['PROPERTIES'] = [];
        $element['DISPLAY_PROPERTIES'] = [];
        $element['PRODUCT_PROPERTIES'] = [];
        $element['PRODUCT_PROPERTIES_FILL'] = [];
        $element['OFFERS'] = [];
        $element['OFFER_ID_SELECTED'] = 0;

        if (!empty($this->storage['CATALOGS'][$element['IBLOCK_ID']])){
            $element['CHECK_QUANTITY'] = $this->isNeedCheckQuantity($element['PRODUCT']);
        }

        if ($this->getAction() === 'bigDataLoad'){
            $element['RCM_ID'] = $this->recommendationIdToProduct[$element['ID']];
        }
    }

    protected function getPropertyList($iblock, $propertyCodes)
    {

    }

    protected function getSelect()
    {
        $result = [
            'ID',
            'IBLOCK_ID',
            'CODE',
            'XML_ID',
            'NAME',
            'ACTIVE',
            'SORT',
            'IBLOCK_SECTION_ID',
            'DETAIL_PAGE_URL',
            'DETAIL_PICTURE',
            'PREVIEW_PICTURE'
        ];

        $checkPriceProperties = (
            !$this->useCatalog
            || (
                isset($this->arParams['IBLOCK_ID'])
                && $this->arParams['IBLOCK_ID'] > 0
                && !isset($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
            )
        );

        if ($checkPriceProperties && !empty($this->storage['PRICES'])){
            foreach ($this->storage['PRICES'] as $row){
                if (empty($row['SELECT'])){
                    continue;
                }

                $result[] = $row['SELECT'];
            }
        }

        return $result;
    }

    protected function editTemplateProductSlider(&$item, $iblock, $limit = 0, $addDetailToSlider = true, $default = array())
    {

    }

    protected function editTemplateOfferSlider(&$item, $iblock, $limit = 0, $addDetailToSlider = true, $default = array())
    {

    }

    public function getTemplateEmptyPreview()
    {
        return [];
    }

    protected function getIblockOffers($iblockId)
    {
        $offers = [];
        $iblockParams = $this->storage['IBLOCK_PARAMS'][$iblockId];

        $enableCompatible = $this->isEnableCompatible();

        if (
            $this->useCatalog
            && $this->offerIblockExist($iblockId)
            && !empty($this->productWithOffers[$iblockId])
        ){
            $catalog = $this->storage['CATALOGS'][$iblockId];

            $productProperty = 'PROPERTY_'.$catalog['SKU_PROPERTY_ID'];
            $productPropertyValue = $productProperty.'_VALUE';

            $offersFilter = $this->getOffersFilter($catalog['IBLOCK_ID']);
            $offersFilter[$productProperty] = $this->productWithOffers[$iblockId];

            $offersSelect = [
                'ID',
                $productProperty,
                'AVAILABLE'
            ];

            $query = \CIBlockElement::GetList(
                [],
                $offersFilter,
                false,
                false,
                $offersSelect
            );
            while($row = $query->GetNext(true, false)){
                $row['LINK_ELEMENT_ID'] = (int) $row[$productPropertyValue];
                $offers[$row['ID']] = $row;
            }

            $propertyCodeSize = Property::getCode('SIZE');
            \CIBlockElement::GetPropertyValuesArray(
                $arProperties,
                $catalog['IBLOCK_ID'],
                ['ID' => array_keys($offers)],
                ['CODE' => $propertyCodeSize],
                [
                    'PROPERTY_FIELDS' => ['ID', 'VALUE'],
                    'GET_RAW_DATA' => 'Y'
                ]
            );

            foreach($offers as $offerId => $arOffer){
                $offers[$offerId]['SIZE'] = $arProperties[$offerId][$propertyCodeSize]['VALUE'];
            }

        }

        return $offers;
    }

    protected function processOffers()
    {
        if ($this->useCatalog && !empty($this->iblockProducts)){
            foreach (array_keys($this->iblockProducts) as $iblock){
                if (!empty($this->productWithOffers[$iblock])){
                    $iblockOffers = $this->getIblockOffers($iblock);
                    if(!empty($iblockOffers)){

                        foreach ($this->elements as $key => $arItem){
                            foreach($iblockOffers as $arOffer){
                                if($arOffer['LINK_ELEMENT_ID'] === (int) $arItem['ID']){
                                    $this->elements[$key]['OFFERS'][] = $arOffer;
                                }
                            }
                        }

                    }
                }
            }
        }
    }

    protected function makeOutputResult()
    {
        parent::makeOutputResult();
        $this->arResult['ITEMS'] = $this->getFormattedItems();
    }

    protected function getFormattedItems(): array
    {
        $items = [];

        $dimensions = [400, 533];
        $sizes = [
            'DEFAULT' => $dimensions,
            'DEFAULT_2X' => array_map(function($item){
               return $item * 2;
            }, $dimensions)
        ];

        foreach($sizes as $i => $size){
            $sizes[$i][] = BX_RESIZE_IMAGE_PROPORTIONAL;
        }

        $arBasePrices = $this->getBasePrices();
        foreach($this->arResult['ITEMS'] as $arItem){

            $picture = null;
            $productPicture = $arItem['PREVIEW_PICTURE'] ?: $arItem['DETAIL_PICTURE'];

            if($productPicture){
                $img = new Resize($sizes);
                $img->add($productPicture);
                $arImage = $img->getResizeArray();
                $picture = [
                    'src' => $arImage['RESIZE'][0]['SIZES']['DEFAULT'],
                    'src2x' => $arImage['RESIZE'][0]['SIZES']['DEFAULT_2X'],
                    'width' => $arImage['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'],
                    'height' => $arImage['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']
                ];
            }


            $brandId = $arItem['PROPERTIES'][Property::getCode('BRAND')]['VALUE'];
            $brand = ($brandId > 0) ? Brand::get($brandId) : null;

            $item = [
                'id' => (int) $arItem['ID'],
                'name' => $arItem['NAME'],
                'url' => $arItem['DETAIL_PAGE_URL'],
                'picture' => $picture,
                'available' => $arItem['PRODUCT']['AVAILABLE'] === 'Y',
                'canBuy' => $arItem['CAN_BUY'],
                'size' => $arItem['PROPERTIES'][Property::getCode('SIZE')]['VALUE'],
                'brand' => $brand['NAME'],
                'offers' => [],
                'labels' => []
            ];

            $arPrice = null;
            if((int) $arItem['PRODUCT']['TYPE'] !== Catalog\ProductTable::TYPE_SKU){
                $arPrice = Price::getOptimal($arItem['ID'], $arBasePrices[$arItem['ID']]);
            }

            foreach($arItem['OFFERS'] as $arOffer){

                $arOfferPrice = Price::getOptimal($arOffer['ID'], $arBasePrices[$arOffer['ID']]);
                if(
                    $arOfferPrice['PRICE']
                    && (
                        !$arPrice
                        || ($arOfferPrice['PRICE'] < $arPrice['PRICE'])
                    )
                ){
                    $arPrice = $arOfferPrice;
                }

                $offer = [
                    'id' => (int) $arOffer['ID'],
                    'available' => $arOffer['AVAILABLE'] === 'Y',
                    'canBuy' => ($arOffer['AVAILABLE'] === 'Y') && $arOfferPrice['PRICE'],
                    'size' => $arOffer['SIZE']
                ];

                $item['offers'][] = $offer;

            }

            usort($item['offers'], function($a, $b){
                return $a['size'] <=> $b['size'];
            });

            $item['price'] = [
                'base' => [
                    'raw' => $arPrice['BASE_PRICE'],
                    'formatted' => $arPrice['PRINT_BASE_PRICE']
                ],
                'result' => [
                    'raw' => $arPrice['PRICE'],
                    'formatted' => $arPrice['PRINT_PRICE']
                ],
                'discount' => [
                    'raw' => $arPrice['DISCOUNT'],
                    'formatted' => $arPrice['PRINT_DISCOUNT']
                ],
                'percent' => $arPrice['PERCENT'],
                'currency' => $arPrice['CURRENCY']
            ];

            if($arPrice['PERCENT'] > 0){
                $item['labels'][] = [
                    'code' => 'discount',
                    'label' => $arPrice['PERCENT'] . '%'
                ];
            }

            foreach($this->arParams['LABEL_PROP'] as $propertyCode){

                if(!$arItem['PROPERTIES'][$propertyCode]['VALUE']){
                    continue;
                }

                $item['labels'][] = [
                    'code' => Text::toCamelCase($propertyCode),
                    'label' => Text::toCamelCase($propertyCode)
                ];
            }

            $items[] = $item;

        }

        return $items;
    }

    protected function getBasePrices(): array
    {
        $arPrices = [];

        $ids = [];
        foreach($this->arResult['ITEMS'] as $arItem){

            $ids[] = $arItem['ID'];

            foreach($arItem['OFFERS'] as $arOffer){
                $ids[] = $arOffer['ID'];
            }

        }

        $query = PriceTable::getList(
            [
                'select' => ['PRODUCT_ID', 'PRICE_SCALE'],
                'filter' => [
                    '=PRODUCT_ID' => $ids,
                    '=CATALOG_GROUP_ID' => PriceHelper::getId(PriceHelper::GROUP_BASE)
                ]
            ]
        );
        while($row = $query->fetch()){
            $arPrices[$row['PRODUCT_ID']] = $row;
        }

        return $arPrices;
    }
}