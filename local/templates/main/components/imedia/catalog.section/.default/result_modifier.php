<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Context;

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$showPagination = false;
if (
    ($arParams['PAGE_ELEMENT_COUNT'] > 0)
    && ($arResult['NAV_RESULT']->NavPageCount > 1)
    && (
        $arParams['DISPLAY_TOP_PAGER']
        || $arParams['DISPLAY_BOTTOM_PAGER']
    )
){
    $showPagination = true;
}

$request = Context::getCurrent()->getRequest();

$queryList = [];
foreach($request->getQueryList() as $key => $value){
    if($key === 'PAGEN_' . $arResult['NAV_RESULT']->NavNum){
        continue;
    }

    if(
        ($key === $value)
        || (strpos($value, $key) !== false)
    ){
        continue;
    }

    $queryList[] = $key .'='. $value;
}

$arResult['PAGINATION'] = [
    'show' => $showPagination,
    'recordsCount' => $arResult['NAV_RESULT']->NavRecordCount,
    'pageCount' => $arResult['NAV_RESULT']->NavPageCount,
    'pageSize' => $arResult['NAV_RESULT']->NavPageSize,
    'navNum' => $arResult['NAV_RESULT']->NavNum,
    'currentPage' => $arResult['NAV_RESULT']->NavPageNomer,
    'path' => $request->getRequestedPageDirectory() . '/',
    'query' => implode('&', $queryList)
];

if (
    defined('BX_COMP_MANAGED_CACHE')
    && is_object($GLOBALS['CACHE_MANAGER'])
){
    $cp =& $this->__component;
    if (strlen($cp->getCachePath())){
        $GLOBALS['CACHE_MANAGER']->RegisterTag('PAGINATION');
    }
}

if(
    ($arParams['SET_CANONICAL'] === 'Y') &&
    ((int) $arResult['NAV_RESULT']->NavPageNomer === 1)
){
    $this->__component->SetResultCacheKeys(['SECTION_PAGE_URL']);
}

$this->__component->SetResultCacheKeys(['ITEMS', 'PAGINATION', 'CACHED_TPL', 'UF_DESCRIPTION_TOP']);