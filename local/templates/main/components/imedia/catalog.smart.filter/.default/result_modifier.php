<?php
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Catalog\Color;
use Imedia\Main\Helpers\Catalog\Filter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$propertyCodeColor = Property::getCode('COLOR');

if(!empty($arParams['HIDDEN_PROPERTIES'])){
    foreach($arResult['ITEMS'] as $key => $arItem){
        if(in_array($arItem['CODE'], $arParams['HIDDEN_PROPERTIES'], true)){
            unset($arResult['ITEMS'][$key]);
        }
    }
}

$arResult['PROPERTIES'] = Filter::getConfig();

foreach($arResult['PROPERTIES'] as $i => $arProperty){

    if($arProperty['code'] === 'price'){

        foreach($arResult['ITEMS'] as $arItem){
            if(!$arItem['PRICE']){
                continue;
            }

            $arCodeList = ['MIN', 'MAX'];
            foreach($arCodeList as $code){

                if(!$arItem['VALUES'][$code]['HTML_VALUE']){
                    $arItem['VALUES'][$code]['HTML_VALUE'] = null;
                }

                $arResult['PROPERTIES'][$i]['value'][strtolower($code)] = $arItem['VALUES'][$code];
                $arResult['PROPERTIES'][$i]['limit'][strtolower($code)] = $arItem['VALUES'][$code]['VALUE'];

            }

        }

        if(
            !(
                isset($arResult['PROPERTIES'][$i]['value']['min'])
                && isset($arResult['PROPERTIES'][$i]['value']['max'])
            )
        ){
            unset($arResult['PROPERTIES'][$i]);
        }

    } else {

        foreach($arProperty['cols'] as $j => $arCatalogProperty){

            foreach($arResult['ITEMS'] as $arItem){
                if($arItem['CODE'] !== $arCatalogProperty['code']){
                    continue;
                }

                $arResult['PROPERTIES'][$i]['cols'][$j]['type'] = $arItem['DISPLAY_TYPE'];

                foreach($arItem['VALUES'] as $value){

                    if(!$value['DISABLED']){
                        $value['DISABLED'] = false;
                    }

                    if(!$value['CHECKED']){
                        $value['CHECKED'] = false;
                    }

                    if($arItem['CODE'] === $propertyCodeColor){
                        $arColor = Color::get($value['VALUE']);
                        $value['PICTURE'] = $arColor['PICTURE'];
                    }

                    $arResult['PROPERTIES'][$i]['cols'][$j]['values'][] = $value;

                }

                break;
            }

            if(empty($arResult['PROPERTIES'][$i]['cols'][$j]['values'])){
                unset($arResult['PROPERTIES'][$i]['cols'][$j]);
            } else {

                usort($arResult['PROPERTIES'][$i]['cols'][$j]['values'], function($a, $b){

                    if($a['CHECKED'] === $b['CHECKED']){
                        return $a['UPPER'] <=> $b['UPPER'];
                    }

                    return $b['CHECKED'] <=> $a['CHECKED'];

                });

            }

        }

        if(empty($arResult['PROPERTIES'][$i]['cols'])){
            unset($arResult['PROPERTIES'][$i]);
        } else {
            sort($arResult['PROPERTIES'][$i]['cols']);
        }

    }

}

sort($arResult['PROPERTIES']);