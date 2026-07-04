<?php

namespace Imedia\Component;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Imedia\Main\Helpers\Catalog\Menu;
use Imedia\Main\Helpers\Catalog\Selected;
use Imedia\Main\Helpers\Component\CatalogMenuAside as CatalogMenuAsideTrait;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class SearchMenuAside extends \CBitrixComponent
{
    use CatalogMenuAsideTrait;

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
        $this->arResult = $this->filterMenu($arCatalogMenu[$selectedCatalog]);
        $this->arResult['SELECTED_SECTIONS'] = $this->getSelectedSections();
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

    protected function filterMenu(array $arCatalogMenu): ?array
    {
        $arSections = array_fill_keys(array_values($this->arParams['SECTIONS']), 1);
        return $this->_filterMenu($arCatalogMenu, $arSections);
    }

    protected function _filterMenu(array $arItem, array $arSections): ?array
    {
        $arItems = [];
        foreach($arItem['ITEMS'] as $arChild){

            $arFilteredChild = static::_filterMenu($arChild, $arSections);
            if($arFilteredChild){
                $arItems[] = $arFilteredChild;
            }

        }

        $arItem['ITEMS'] = $arItems;

        if(empty($arItem['ITEMS']) && !isset($arSections[$arItem['ID']])){
            return null;
        }

        $uri = new Uri(str_replace('/catalog/', $this->arParams['SEF_FOLDER'], $arItem['LINK']));
        $uri->addParams(
            [
                $this->arParams['PARAM_QUERY'] => $this->arParams['QUERY']
            ]
        );

        $arItem['LINK'] = $uri->getUri();
        return $arItem;
    }
}