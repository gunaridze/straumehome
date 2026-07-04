<?php
use Imedia\Main\Helpers\Catalog\Selected;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$arResult['SELECTED_ITEM'] = $this->getComponent()->getSelectedItem($arResult);

if(empty($arResult['SELECTED_ITEM']['PARENT'])){

    $arResult['SELECTED_ITEM']['ITEMS'] = $arResult['ITEMS'];

    if(empty($arResult['SELECTED_ITEM']['ITEMS'])){
        foreach(Selected::getList() as $arParent){
            if((int) $arParent['ID'] === Selected::get()){
                $arResult['SELECTED_ITEM']['PARENT'] = [
                    'NAME' => $arParent['NAME'],
                    'LINK' => SITE_DIR . $arParent['CODE'] . '/'
                ];
            }
        }
    }

}

if(!$arResult['SELECTED_ITEM']['NAME']){
    $arResult['SELECTED_ITEM']['NAME'] = $arParams['TITLE'];
}