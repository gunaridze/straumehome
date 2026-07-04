<?php
namespace Imedia\Main\Helpers\Catalog;

use Imedia\Main\Helpers\Formatter\Text;

class Product
{
    public static function getOfferData(array $arOffer, array $arResult, array $arParams)
    {
        $offer = [
            'id' => $arOffer['ID'],
            'tree' => $arOffer['TREE'],
            'name' => $arOffer['NAME'],
            'link' => $arOffer['DETAIL_PAGE_URL'],
            'labels' => [],
            'price' => [
                'base' => $arOffer['OPTIMAL_PRICE']['PRINT_BASE_PRICE'],
                'result' => $arOffer['OPTIMAL_PRICE']['PRINT_PRICE'],
                'discountPercent' => $arOffer['OPTIMAL_PRICE']['PERCENT']
            ],
            'available' => $arOffer['PRODUCT']['AVAILABLE'] === 'Y',
            'canBuy' => $arOffer['CAN_BUY'],
            'size' => $arOffer['PROPERTIES'][Property::getCode('SIZE')]['VALUE'],
            'mainProperties' => []
        ];

        if($arOffer['OPTIMAL_PRICE']['PERCENT'] > 0){

            $offer['labels'][] = [
                'code' => 'discount',
                'label' => $arOffer['OPTIMAL_PRICE']['PERCENT'] . '%'
            ];

        }

        foreach($arResult['LABEL_PROP'] as $code){

            if(!$arOffer['PROPERTIES'][$code]['VALUE']){
                continue;
            }

            $offer['labels'][] = [
                'code' => Text::toCamelCase($code),
                'label' => Text::toCamelCase($code)
            ];

        }

        foreach($arParams['PRODUCT_MAIN_PROPERTIES'] as $code){
            $value = ($arOffer['PROPERTIES'][$code]['VALUE']) ?: $arResult['PROPERTIES'][$code]['VALUE'];

            if(!$value){
                continue;
            }

            $offer['mainProperties'][] = [
                'label' => $arResult['PROPERTIES'][$code]['NAME'],
                'value' => (is_array($value))
                    ? implode(', ', $value)
                    : strip_tags($value)
            ];

        }

        return $offer;
    }
}