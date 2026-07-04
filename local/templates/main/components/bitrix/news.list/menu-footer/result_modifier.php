<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

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

$arSections = [];

$arSort = ['SORT' => 'ASC'];

$arFilter = [
    'ACTIVE' => 'Y',
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'DEPTH_LEVEL' => 1,
    '!UF_COLUMN' => false
];

$arSelect = [
    'ID',
    'SORT',
    'NAME',
    'UF_COLUMN',
    'UF_CATALOG',
    'UF_LINK'
];

$arCols = [];

$query = \CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
while($row = $query->GetNext(true, false)){

    if(
        $row['UF_CATALOG']
        && ((int) $row['UF_CATALOG'] !== $arParams['SELECTED_CATALOG'])
    ){
        continue;
    }

    $columnNumber = (int) $row['UF_COLUMN'];

    foreach($arResult['ITEMS'] as $arItem){

        if((int) $arItem['IBLOCK_SECTION_ID'] !== (int) $row['ID']){
            continue;
        }

        $row['ITEMS'][] = $arItem;

    }

    $arCols[$columnNumber][] = $row;

}

$arResult['ITEMS'] = [];
for($i = 1; $i < 4; $i++){
    $arResult['ITEMS'][] = $arCols[$i];
}