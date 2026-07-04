<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arResult['POPOVER_SECTIONS'] = [];
foreach($arResult['ITEMS'] as $arItem){
    if(empty($arItem['ITEMS'])){
        continue;
    }

    $arResult['POPOVER_SECTIONS'][] = $arItem['ID'];
}

if (
    defined('BX_COMP_MANAGED_CACHE')
    && is_object($GLOBALS['CACHE_MANAGER'])
){
    $cp =& $this->__component;
    if (strlen($cp->getCachePath())){
        $GLOBALS['CACHE_MANAGER']->RegisterTag('POPOVER_SECTIONS');
    }
}

$this->__component->SetResultCacheKeys(['POPOVER_SECTIONS', 'CACHED_TPL']);