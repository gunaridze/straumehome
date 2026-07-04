<?php

namespace Imedia\Component;

use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Imedia\Main\Helpers\Catalog\Element;
use Imedia\Main\Helpers\Catalog\Price;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Formatter\Text;
use Imedia\Main\Helpers\Iblock\Brand;
use Imedia\Main\Helpers\Image\Resize;
use Imedia\Main\Helpers\Iblock\Seo;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

\CBitrixComponent::includeComponentClass('bitrix:catalog.element');

class CatalogElement extends \CatalogElementComponent
{
    protected function editTemplateData()
    {
        $this->arResult['DEFAULT_PICTURE'] = $this->getTemplateEmptyPreview();
        $this->arResult['SKU_PROPS'] = $this->getTemplateSkuPropList();
        $this->arResult['CURRENCIES'] = $this->getTemplateCurrencies();
        $this->editTemplateItems($this->arResult);

        $this->arResult = array_merge($this->arResult, $this->getResultModifier());
    }

    protected function getResultModifier(): array
    {
        $result = [];

        $result['PROPERTY_CODE_SKU'] = Property::getCode('SKU');
        $result['PROPERTY_CODE_GTIN'] = Property::getCode('GTIN');

        $result['BASE_CURRENCY'] = CurrencyManager::getBaseCurrency();

        if(!empty($this->arResult['DETAIL_PICTURE'])) {
            $dimensions = [
                'width' => 464,
                'height' => 670
            ];

            $sizes = [
                'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_PROPORTIONAL],
                'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_PROPORTIONAL]
            ];

            $result['GALLERY'][] = Resize::setSelfResizeArray(
                $this->arResult['DETAIL_PICTURE'],
                $sizes
            );
        }

        $vimeoId = $this->arResult['PROPERTIES']['VIMEO_ID']['VALUE'];
        if(!empty($vimeoId)) {
            $result['GALLERY'][] = [
                'ID' => $vimeoId,
                'CONTENT_TYPE' => 'video',
                'SRC' => "https://player.vimeo.com/video/{$vimeoId}?background=1"
            ];
        }

        foreach($this->arResult['MORE_PHOTO'] as $arPhoto) {
            $dimensions = [
                'width' => 464,
                'height' => 670
            ];

            $sizes = [
                'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_PROPORTIONAL],
                'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_PROPORTIONAL]
            ];

            $result['GALLERY'][] = Resize::setSelfResizeArray(
                $arPhoto,
                $sizes
            );
        }

        $brandId = $this->arResult['PROPERTIES'][Property::getCode('BRAND')]['VALUE'];
        $result['BRAND'] = $brandId > 0 ? Brand::get($brandId) : null;
        if($result['BRAND']['PREVIEW_PICTURE'] > 0) {
            $dimensions = [
                'width' => 76,
                'height' => 38
            ];

            $sizes = [
                'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_PROPORTIONAL],
                'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_PROPORTIONAL]
            ];

            $img = new Resize($sizes);
            $img->add($result['BRAND']['PREVIEW_PICTURE']);
            $result['BRAND']['PICTURE'] = $img->getResizeArray();

            $dimensionsRound = [
                'width' => 40,
                'height' => 40
            ];

            $sizesRound = [
                'DEFAULT' => [$dimensionsRound['width'], $dimensionsRound['height'], BX_RESIZE_IMAGE_PROPORTIONAL],
                'DEFAULT_2X' => [$dimensionsRound['width'] * 2, $dimensionsRound['height'] * 2, BX_RESIZE_IMAGE_PROPORTIONAL]
            ];

            $img = new Resize($sizesRound);
            $img->add($result['BRAND']['PREVIEW_PICTURE']);
            $result['BRAND']['PICTURE_ROUND'] = $img->getResizeArray();
        }

        $arPrice = Price::getOptimal($this->arResult['ID']);

        $result['OFFERS'] = $this->arResult['OFFERS'];
        foreach($result['OFFERS'] as &$arOffer) {
            $arOfferPrice = Price::getOptimal($arOffer['ID']);

            $arOffer['OPTIMAL_PRICE'] = $arOfferPrice;

            $result['JS_OFFERS'][] = [
                'ID' => $arOffer['ID'],
                'TREE' => $arOffer['TREE'],
                'OPTIMAL_PRICE' => $arOfferPrice,
                'CAN_BUY' => $arOffer['CAN_BUY']
            ];

            if(!$arPrice || ($arOfferPrice['PRICE'] < $arPrice['PRICE'])){
                $arPrice = $arOfferPrice;
            }
        }
        unset($arOffer);

        $result['OPTIMAL_PRICE'] = $arPrice;

        foreach($this->arParams['LABEL_PROP'] as $propertyCode) {
            if(!$this->arResult['PROPERTIES'][$propertyCode]['VALUE']) {
                continue;
            }

            $result['LABELS'][] = [
                'code' => Text::toCamelCase($propertyCode),
                'label' => Text::toCamelCase($propertyCode)
            ];
        }

        $linkCode = $this->arResult['PROPERTIES'][Property::getCode('LINK_CODE')]['VALUE'];
        $result['LINK_PRODUCTS'] = !empty($linkCode) ? Element::getProductsByLinkCode($linkCode) : null;

        $deliveryMethods = $this->arResult['PROPERTIES']['DELIVERY_METHODS']['VALUE'];
        $result['DELIVERY_METHODS'] = Element::getDeliveryMethods(!empty($deliveryMethods) ? $deliveryMethods : []);

        $result['SECTION'] = $this->arResult['SECTION'];
        if($this->arResult['SECTION']['PICTURE'] > 0) {
            $dimensions = [
                'width' => 40,
                'height' => 40
            ];

            $sizes = [
                'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
                'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
            ];

            $result['SECTION']['PICTURE'] = Resize::setSelfResizeArray(
                $this->arResult['SECTION']['PICTURE'],
                $sizes
            );
        }

        $result['DISPLAY_PROPERTIES'] = $this->arResult['DISPLAY_PROPERTIES'];
        if(!empty($result['DISPLAY_PROPERTIES'])) {
            $arPropLink = \CIBlockSectionPropertyLink::GetArray($this->arParams['IBLOCK_ID'], $this->arResult['IBLOCK_SECTION_ID']);
            foreach($result['DISPLAY_PROPERTIES'] as &$arProp) {
                $arProp['FILTER_HINT'] = $arPropLink[$arProp['ID']]['FILTER_HINT'];
            }
            unset($arProp);

            $result['PROPS_GROUPS'] = Element::getPropsGroups($result['DISPLAY_PROPERTIES']);

            foreach($result['OFFERS'] as $key => &$arOffer) {
                $arOffer['PROPS_GROUPS'] = $result['PROPS_GROUPS'];
                foreach ($arOffer['PROPS_GROUPS'] as &$arGroup) {
                    foreach ($arGroup['PROPERTIES'] as &$arProp) {
                        if(!$arOffer['DISPLAY_PROPERTIES'][$arProp['CODE']]['VALUE'])
                            continue;

                        $arProp = $arOffer['DISPLAY_PROPERTIES'][$arProp['CODE']];
                        $arProp['FILTER_HINT'] = $result['DISPLAY_PROPERTIES'][$arProp['CODE']]['FILTER_HINT'];
                    }
                    unset($arProp);
                }
                unset($arGroup);

                $offerPropList = $result['DISPLAY_PROPERTIES'];
                foreach($offerPropList as &$arProp) {
                    if(!$arOffer['DISPLAY_PROPERTIES'][$arProp['CODE']]['VALUE'])
                        continue;

                    $arProp = $arOffer['DISPLAY_PROPERTIES'][$arProp['CODE']];
                    $arProp['FILTER_HINT'] = $result['DISPLAY_PROPERTIES'][$arProp['CODE']]['FILTER_HINT'];
                }
                unset($arProp);

                $arOffer['DISPLAY_PROPERTIES'] = $offerPropList;
                $result['JS_OFFERS'][$key]['PROPERTIES'] = $offerPropList;
            }
            unset($arOffer);
        }

        $cleanCare = $this->arResult['PROPERTIES']['CLEAN_CARE']['VALUE'];
        $result['CLEAN_CARE'] = !empty($cleanCare) ? Element::getCleanCareByCodes($cleanCare) : null;

        if(!empty($this->arResult['PROPERTIES']['RECOMMEND_PRODUCTS']['VALUE'])) {
            $result['RECOMMEND_PRODUCTS_FILTER'] = [
                'ID' => $this->arResult['PROPERTIES']['RECOMMEND_PRODUCTS']['VALUE']
            ];
        } else {
            $result['RECOMMEND_PRODUCTS_FILTER'] = Element::getRecommendProductsFilter($this->arParams['IBLOCK_ID'], $this->arResult['SECTION']['PATH']);
        }

        if(!empty($this->arResult['PROPERTIES']['SIMILAR_PRODUCTS']['VALUE'])) {
            $result['SIMILAR_PRODUCTS_FILTER'] = [
                'ID' => $this->arResult['PROPERTIES']['SIMILAR_PRODUCTS']['VALUE']
            ];
		} else {
			$priceMinValue = $this->arResult['PROPERTIES'][Property::getCode('PRICE_MIN')]['VALUE'];
			$priceMaxValue = $this->arResult['PROPERTIES'][Property::getCode('PRICE_MAX')]['VALUE'];
		
			// исправление ошибки, которая возникает из-за того, что PROPERTY_PRICE_MIN содержит строку, а не число. Проверяем и преобразуем значения в числа
			$priceMin = is_numeric($priceMinValue) ? (float)$priceMinValue * 0.95 : 0;
			$priceMax = is_numeric($priceMaxValue) ? (float)$priceMaxValue * 1.05 : PHP_INT_MAX;
		
			$result['SIMILAR_PRODUCTS_FILTER'] = [
				'>=PROPERTY_PRICE_MIN' => $priceMin,
				'<=PROPERTY_PRICE_MAX' => $priceMax
			];
		}

        $result['SKU_PROPS'] = $this->arResult['SKU_PROPS'];
        foreach($result['SKU_PROPS'] as $key => $arProperty){
            uasort($result['SKU_PROPS'][$key]['VALUES'], function($a, $b){
                return $a['NAME'] <=> $b['NAME'];
            });
        }

        $result['PRICE_MIN'] = $this->arResult['PROPERTIES']['PRICE_MIN']['VALUE'];
        if($result['PRICE_MIN']){

            $result['PRICE_MIN'] = \CCurrencyLang::CurrencyFormat(
                $result['PRICE_MIN'],
                CurrencyManager::getBaseCurrency()
            );

        }

        $this->setResultCacheKeys([
            'RECOMMEND_PRODUCTS_FILTER',
            'SIMILAR_PRODUCTS_FILTER',
            'PRICE_MIN',
            'CACHED_TPL',
            'SHARE_PICTURE'
        ]);

        return $result;
    }

    protected function initMetaData()
    {
        global $APPLICATION;
        $arResult =& $this->arResult;

        if ($this->arParams['SET_CANONICAL_URL'] === 'Y' && $arResult["CANONICAL_PAGE_URL"])
            $APPLICATION->SetPageProperty('canonical', $arResult["CANONICAL_PAGE_URL"]);

        if ($this->arParams['SET_TITLE'])
        {
            $APPLICATION->SetTitle($arResult["META_TAGS"]["TITLE"], $this->storage['TITLE_OPTIONS']);
        }

        if ($this->arParams['SET_BROWSER_TITLE'] === 'Y')
        {
            if ($arResult["META_TAGS"]["BROWSER_TITLE"] !== '')
            {
                $processor = Seo\Factory::create('element');
                $metaTitle = $processor->process($arResult["META_TAGS"]["BROWSER_TITLE"], $arResult);

                $APPLICATION->SetPageProperty("title", $metaTitle, $this->storage['TITLE_OPTIONS']);
            }
        }

        if ($this->arParams['SET_META_KEYWORDS'] === 'Y')
        {
            if ($arResult["META_TAGS"]["KEYWORDS"] !== '')
            {
                $APPLICATION->SetPageProperty("keywords", $arResult["META_TAGS"]["KEYWORDS"], $this->storage['TITLE_OPTIONS']);
            }
        }

        if ($this->arParams['SET_META_DESCRIPTION'] === 'Y')
        {
            if ($arResult["META_TAGS"]["DESCRIPTION"] !== '')
            {
                $processor = Seo\Factory::create('element');
                $metaDescription = $processor->process($arResult['META_TAGS']['DESCRIPTION'], $arResult);

                $APPLICATION->SetPageProperty("description", $metaDescription, $this->storage['TITLE_OPTIONS']);
            }
        }

        if (!empty($arResult['BACKGROUND_IMAGE']) && is_array($arResult['BACKGROUND_IMAGE']))
        {
            $APPLICATION->SetPageProperty(
                'backgroundImage',
                'style="background-image: url(\''.\CHTTP::urnEncode($arResult['BACKGROUND_IMAGE']['SRC'], 'UTF-8').'\')"'
            );
        }

        if ($this->arParams['ADD_SECTIONS_CHAIN'] && !empty($arResult['SECTION']['PATH']) && is_array($arResult['SECTION']['PATH']))
        {
            foreach ($arResult['SECTION']['PATH'] as $path)
            {
                if ($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] != '')
                {
                    $APPLICATION->AddChainItem($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $path['~SECTION_PAGE_URL']);
                }
                else
                {
                    $APPLICATION->AddChainItem($path['NAME'], $path['~SECTION_PAGE_URL']);
                }
            }
        }

        if ($this->arParams['ADD_ELEMENT_CHAIN'])
        {
            $APPLICATION->AddChainItem($arResult["META_TAGS"]["ELEMENT_CHAIN"]);
        }

        if ($this->arParams['SET_LAST_MODIFIED'] && $arResult['TIMESTAMP_X'])
        {
            Main\Context::getCurrent()->getResponse()->setLastModified(DateTime::createFromUserTime($arResult["TIMESTAMP_X"]));
        }
    }

    protected function editTemplateProductSlider(&$item, $iblock, $limit = 0, $addDetailToSlider = true, $default = [])
    {
        $propCode = $this->storage['IBLOCK_PARAMS'][$iblock]['ADD_PICT_PROP'];

        $slider = static::getSliderForItem($item, $propCode, $addDetailToSlider);

        if ($limit > 0){
            $slider = array_slice($slider, 0, $limit);
        }

        $item['SHOW_SLIDER'] = true;
        $item['MORE_PHOTO'] = $slider;
        $item['MORE_PHOTO_COUNT'] = count($slider);
    }

    public static function getSliderForItem(&$item, $propertyCode, $addDetailToSlider, $encode = true)
    {
        $encode = ($encode === true);
        $result = [];

        if (!empty($item) && is_array($item))
        {
            if (
                $propertyCode
                && isset($item['PROPERTIES'][$propertyCode])
                && ('F' === $item['PROPERTIES'][$propertyCode]['PROPERTY_TYPE'])
            ){
                if (('MORE_PHOTO' === $propertyCode) && isset($item['MORE_PHOTO']) && !empty($item['MORE_PHOTO'])){

                    foreach ($item['MORE_PHOTO'] as $onePhoto){
                        $result[] = [
                            'ID' => (int)$onePhoto['ID'],
                            'SRC' => Iblock\Component\Tools::getImageSrc($onePhoto, $encode),
                            'WIDTH' => (int)$onePhoto['WIDTH'],
                            'HEIGHT' => (int)$onePhoto['HEIGHT']
                        ];
                    }
                    unset($onePhoto);

                } elseif(
                    isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE'])
                    && !empty($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE'])
                ) {

                    $fileValues = (
                    isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']['ID']) ?
                        [0 => $item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']] :
                        $item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']
                    );

                    foreach ($fileValues as $oneFileValue){
                        $result[] = [
                            'ID' => (int)$oneFileValue['ID'],
                            'SRC' => Iblock\Component\Tools::getImageSrc($oneFileValue, $encode),
                            'WIDTH' => (int)$oneFileValue['WIDTH'],
                            'HEIGHT' => (int)$oneFileValue['HEIGHT']
                        ];
                    }

                    if (isset($oneFileValue)){
                        unset($oneFileValue);
                    }
                } else {

                    $propValues = $item['PROPERTIES'][$propertyCode]['VALUE'];

                    if (!is_array($propValues)){
                        $propValues = array($propValues);
                    }

                    foreach ($propValues as $oneValue){

                        $oneFileValue = \CFile::GetFileArray($oneValue);

                        if (isset($oneFileValue['ID'])){
                            $result[] = [
                                'ID' => (int)$oneFileValue['ID'],
                                'SRC' => Iblock\Component\Tools::getImageSrc($oneFileValue, $encode),
                                'WIDTH' => (int)$oneFileValue['WIDTH'],
                                'HEIGHT' => (int)$oneFileValue['HEIGHT']
                            ];
                        }

                    }

                    if (isset($oneValue)){
                        unset($oneValue);
                    }

                }
            }

            if ($addDetailToSlider){

                if (!empty($item['DETAIL_PICTURE'])){

                    if (!is_array($item['DETAIL_PICTURE'])){
                        $item['DETAIL_PICTURE'] = \CFile::GetFileArray($item['DETAIL_PICTURE']);
                    }

                    if (isset($item['DETAIL_PICTURE']['ID'])){
                        array_unshift(
                            $result,
                            [
                                'ID' => (int)$item['DETAIL_PICTURE']['ID'],
                                'SRC' => Iblock\Component\Tools::getImageSrc($item['DETAIL_PICTURE'], $encode),
                                'WIDTH' => (int)$item['DETAIL_PICTURE']['WIDTH'],
                                'HEIGHT' => (int)$item['DETAIL_PICTURE']['HEIGHT']
                            ]
                        );
                    }
                }
            }
        }
        return $result;
    }
}