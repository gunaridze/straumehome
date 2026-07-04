<?php
use Bitrix\Main\Localization\Loc;

@include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/catalog/.parameters.php';

$arIBlock = [];
$rsIBlock = \CIBlock::GetList(['SORT' => 'ASC'], ['=ACTIVE' => 'Y']);
while ($arr = $rsIBlock->Fetch()){
    $id = (int) $arr['ID'];
    $arIBlock[$id] = '['.$id.'] '. $arr['NAME'];
}

$arUnsetPath = [
    'sections',
    'section',
    'element',
    'compare'
];
foreach($arUnsetPath as $path){
    unset($arComponentParameters['PARAMETERS']['SEF_MODE'][$path]);
}

$arComponentParameters['PARAMETERS']['SEF_MODE']['index'] = [
    'NAME' => Loc::getMessage('IM_SEARCH_PATH_INDEX'),
    'DEFAULT' => '',
    'VARIABLES' => []
];

$arComponentParameters['PARAMETERS']['SEF_MODE']['brands'] = [
    'NAME' => Loc::getMessage('IM_SEARCH_PATH_BRANDS'),
    'DEFAULT' => 'brands/',
    'VARIABLES' => []
];

$arComponentParameters['PARAMETERS']['SEF_MODE']['collections'] = [
    'NAME' => Loc::getMessage('IM_SEARCH_PATH_COLLECTIONS'),
    'DEFAULT' => 'collections/',
    'VARIABLES' => []
];

$arComponentParameters['PARAMETERS']['SEF_MODE']['articles'] = [
    'NAME' => Loc::getMessage('IM_SEARCH_PATH_ARTICLES'),
    'DEFAULT' => 'articles/',
    'VARIABLES' => []
];

$arComponentParameters['PARAMETERS']['IBLOCK_ID_BRANDS'] = [
    "PARENT" => "BASE",
    "NAME" => Loc::getMessage('IM_SEARCH_IBLOCK_BRANDS'),
    "TYPE" => "LIST",
    "ADDITIONAL_VALUES" => "Y",
    "VALUES" => $arIBlock,
    "REFRESH" => "Y"
];

$arComponentParameters['PARAMETERS']['IBLOCK_ID_COLLECTIONS'] = [
    "PARENT" => "BASE",
    "NAME" => Loc::getMessage('IM_SEARCH_IBLOCK_COLLECTIONS'),
    "TYPE" => "LIST",
    "ADDITIONAL_VALUES" => "Y",
    "VALUES" => $arIBlock,
    "REFRESH" => "Y"
];

$arComponentParameters['PARAMETERS']['IBLOCK_ID_ARTICLES'] = [
    "PARENT" => "BASE",
    "NAME" => Loc::getMessage('IM_SEARCH_IBLOCK_ARTICLES'),
    "TYPE" => "LIST",
    "ADDITIONAL_VALUES" => "Y",
    "VALUES" => $arIBlock,
    "REFRESH" => "Y"
];

$arComponentParameters['PARAMETERS']['PARAM_QUERY'] = [
    "PARENT" => "BASE",
    "NAME" => Loc::getMessage('IM_SEARCH_PARAM_QUERY'),
    'TYPE' => 'STRING',
    'DEFAULT' => 'q'
];

$arComponentParameters['PARAMETERS']['MIN_GOOD_RESULT_COUNT'] = [
    "PARENT" => "BASE",
    "NAME" => Loc::getMessage('IM_SEARCH_MIN_GOOD_RESULT_COUNT'),
    'TYPE' => 'STRING',
    'DEFAULT' => '10'
];