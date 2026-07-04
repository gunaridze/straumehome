<?php

namespace Imedia\Component;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Imedia\Main\Helpers\Catalog\Menu;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Catalog\Selected;
use Imedia\Main\Helpers\Iblock\Facet;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Iblock\Brand;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class CatalogMenu extends \CBitrixComponent
{
    protected function checkModules()
    {
        if (!Loader::includeModule('imedia.main')) {
            throw new \Exception(Loc::getMessage('IMEDIA_MAIN_MODULE_NOT_INSTALLED'));
        }
    }

    function getResult()
    {
        $arCatalogMenu = Menu::get();
        $selectedCatalog = Selected::get();
        $this->arResult = $this->updateMenu($arCatalogMenu[$selectedCatalog]);
    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->checkModules();
            $this->getResult();
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }

    protected function updateMenu(array $arMenu): array
    {
        if($this->arParams['SHORT'] === 'Y'){

            $arMenu['ITEMS'] = [];

            if(!$arMenu['HIDE_LINK_SALE']){
                $arMenu['ITEMS'][] = $this->getLinkSale($arMenu);
            }

            if(!$arMenu['HIDE_LINK_NEW']){
                $arMenu['ITEMS'][] = $this->getLinkNew($arMenu);
            }

            $arMenu = $arMenu['ITEMS'];

        } else {
            $arBrands = $this->getBrands($arMenu);

            foreach($arMenu['ITEMS'] as $key => $arItem){

                if(!empty($arItem['ITEMS'])){

                    $arMenu['ITEMS'][$key]['TITLE_PLURAL'] = ($arItem['LABEL_PLURAL'])
                        ? $arItem['LABEL_PLURAL'] . ' ' . mb_strtolower($arItem['NAME'])
                        : $arItem['NAME'];

                    $arMenu['ITEMS'][$key]['LINK_BRANDS'] = str_replace($this->arParams['LINK_CATALOG'], $this->arParams['LINK_BRANDS'], $arItem['LINK']);

                    $arMenu['ITEMS'][$key]['ITEMS'] = $this->getMenuCols($arItem, $arBrands);

                }

            }

            $arMenu['ITEMS'][] = $this->getLinkBrands($arMenu);

            if(!$arMenu['HIDE_LINK_NEW']){
                $arMenu['ITEMS'][] = $this->getLinkNew($arMenu);
            }

            if(!$arMenu['HIDE_LINK_SALE']){
                $arMenu['ITEMS'][] = $this->getLinkSale($arMenu);
            }

            $arMenu['ITEMS'][] = $this->getLinkBlog($arMenu);
            $arMenu['ITEMS'][] = $this->getLinkNews($arMenu);
        }

        return $arMenu;
    }

    protected function getMenuCols(array $arItem, array $arBrands): array
    {
        $arResult = [];

        switch($arItem['MENU_TYPE']){

            case 'sections':

                $arSectionsLinks = $this->getSectionsLinks($arItem['ITEMS']);
                if(!empty($arSectionsLinks)){
                    $arResult['SECTIONS'] = $arSectionsLinks;
                }

                break;

            default:

                $arMainLinks = $this->getMainLinks($arItem['ITEMS']);
                if(!empty($arMainLinks)){
                    $arResult['MAIN'] = $arMainLinks;
                }

                $arPopularLinks = $this->getPopularLinks($arItem['ITEMS']);
                if(!empty($arPopularLinks)){
                    $arResult['POPULAR'] = $arPopularLinks;
                }

                $arBrandsLinks = $this->getBrandsLinks($arItem, $arBrands);
                if(!empty($arBrandsLinks)){
                    $arResult['BRANDS'] = $arBrandsLinks;
                }

                break;

        }

        return $arResult;
    }

    protected function getMainLinks(array $arItems): array
    {
        $arResult = [];

        $maxCount = 12;
        $currentCount = 0;

        foreach($arItems as $arItem){

            if($arItem['SHOW_IN_MENU_MAIN']){

                $arResult[] = static::getPreparedItem($arItem);

                if(++$currentCount === $maxCount){
                    break;
                }

            }

            foreach($arItem['ITEMS'] as $arChild){

                if(!$arChild['SHOW_IN_MENU_MAIN']){
                    continue;
                }

                $arResult[] = static::getPreparedItem($arChild);

                if(++$currentCount === $maxCount){
                    break 2;
                }

            }

        }

        return $arResult;
    }

    protected function getPopularLinks(array $arItems): array
    {
        $arResult = [];

        $maxCount = 13;
        $currentCount = 0;

        foreach($arItems as $arItem){

            if($arItem['IS_POPULAR']){

                $arResult[] = static::getPreparedItem($arItem);

                if(++$currentCount === $maxCount){
                    break;
                }

            }

            foreach($arItem['ITEMS'] as $arChild){

                if(!$arChild['IS_POPULAR']){
                    continue;
                }

                $arResult[] = static::getPreparedItem($arChild);

                if(++$currentCount === $maxCount){
                    break 2;
                }

            }

        }

        return $arResult;
    }

    protected static function getPreparedItem(array $arItem): array
    {
        $allowFields = ['ID', 'NAME', 'SORT', 'LINK'];

        $arPreparedItem = [];

        foreach($allowFields as $code){
            $arPreparedItem[$code] = $arItem[$code];
        }

        return $arPreparedItem;
    }

    protected function getBrandsLinks(array $arItem, array $arBrands): array
    {
        $arResult = [];

        $maxCount = 12;
        $currentCount = 0;

        foreach($arBrands[$arItem['ID']] as $arBrand){

            $arResult[] = [
                'NAME' => $arBrand['NAME'],
                'LINK' => $arItem['LINK']
                    . 'filter/'
                    . strtolower(Property::getCode('BRAND'))
                    . '-is-'
                    . $arBrand['CODE']
                    . '/apply/'
            ];

            if(++$currentCount === $maxCount){
                break;
            }
        }

        return $arResult;
    }

    protected function getLinkBrands(array $arMenu): array
    {
        return [
            'NAME' => Loc::getMessage('IMEDIA_CATALOG_MENU_LINK_BRANDS'),
            'LINK' => str_replace($this->arParams['LINK_CATALOG'], $this->arParams['LINK_BRANDS'], $arMenu['LINK'])
        ];
    }

    protected function getLinkNew(array $arMenu): array
    {
        return [
            'NAME' => Loc::getMessage('IMEDIA_CATALOG_MENU_LINK_NEW'),
            'LINK' => mb_substr($arMenu['LINK'], 0, -1) . '_' . $this->arParams['LINK_NEW']
        ];
    }

    protected function getLinkSale(array $arMenu): array
    {
        return [
            'NAME' => Loc::getMessage('IMEDIA_CATALOG_MENU_LINK_SALE'),
            'LINK' => mb_substr($arMenu['LINK'], 0, -1) . '_' . $this->arParams['LINK_SALE'],
            'IS_PRIMARY' => true
        ];
    }

    protected function getLinkBlog(array $arMenu): array
    {
        return [
            'NAME' => Loc::getMessage('IMEDIA_CATALOG_MENU_LINK_BLOG'),
            'LINK' => $this->arParams['LINK_BLOG'] /*. '?tags=' . mb_strtolower($arMenu['NAME'])*/
        ];
    }

    protected function getLinkNews(array $arMenu): array
    {
        return [
            'NAME' => Loc::getMessage('IMEDIA_CATALOG_MENU_LINK_NEWS'),
            'LINK' => $this->arParams['LINK_NEWS'] /*. '?tags=' . mb_strtolower($arMenu['NAME'])*/
        ];
    }

    protected function getBrands(array $arMenu): array
    {
        $arSectionsBrands = [];

        $cache = Cache::createInstance();

        $cacheId = 'menu-brands-' . $arMenu['ID'];
        $cacheTtl = 3600;
        $cacheDir = '/catalog';

        if ($cache->initCache($cacheTtl, $cacheId, $cacheDir)) {
            $arSectionsBrands = $cache->getVars();
        } elseif ($cache->startDataCache()) {

            foreach($arMenu['ITEMS'] as $arItem){
                $arSectionsBrands[$arItem['ID']] = [];
            }

            foreach($arSectionsBrands as $sectionId => $value){
                $arSectionsBrands[$sectionId] = Facet::getValuesFromPropertyInSection(
                    IblockHelper::getId('CATALOG'),
                    Property::getCode('BRAND'),
                    $sectionId
                );
            }

            Loader::includeModule('iblock');

            $arFilter = [
                'IBLOCK_ID' => IblockHelper::getId('CATALOG'),
                'ID' => array_keys($arSectionsBrands)
            ];

            $arSelect = ['ID', 'UF_BRANDS'];

            $query = \CIBlockSection::GetList([], $arFilter, false, $arSelect);
            while($row = $query->GetNext(true, false)){
                $arSectionsBrands[$row['ID']] = array_intersect($arSectionsBrands[$row['ID']], $row['UF_BRANDS']);
            }

            foreach($arSectionsBrands as $sectionId => $arSection){

                $arBrands = [];

                foreach($arSection as $brandId){
                    $arBrand = Brand::get($brandId);
                    if($arBrand){
                        $arBrands[] = $arBrand;
                    }
                }

                $arSectionsBrands[$sectionId] = $arBrands;

            }

            $taggedCache = Application::getInstance()->getTaggedCache();
            $taggedCache->startTagCache($cacheDir);
            $taggedCache->registerTag('iblock_id_' . IblockHelper::getId('BRANDS'));
            foreach($arSectionsBrands as $sectionId => $arBrands){
                $taggedCache->registerTag('iblock_section_id_' . $sectionId);
            }
            $taggedCache->endTagCache();
            $cache->endDataCache($arSectionsBrands);

        }

        return $arSectionsBrands;

    }

    protected function getSectionsLinks(array $arItems): array
    {
        $arSections = [];

        foreach($arItems as $arItem){

            if(!$arItem['SHOW_IN_MENU_MAIN']){
                continue;
            }

            $arSections[] = [
                'ID' => $arItem['ID'],
                'NAME' => $arItem['NAME'],
                'LINK' => $arItem['LINK'],
                'ITEMS' => $this->getMainLinks($arItem['ITEMS'])
            ];

        }

        return $arSections;
    }
}