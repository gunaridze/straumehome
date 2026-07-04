<?php

namespace Imedia\Component;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Catalog\Menu;
use Imedia\Main\Helpers\Catalog\Selected;
use Imedia\Main\Helpers\Component\CatalogMenuAside as CatalogMenuAsideTrait;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class SectionVirtualMenuAside extends \CBitrixComponent
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
        $arCatalogMenu = $arCatalogMenu[$selectedCatalog];
        $arCatalogMenu = $this->filterMenu($arCatalogMenu);
        $this->arResult = $arCatalogMenu;
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
        return $this->_filterMenu(['ITEMS' => $arCatalogMenu['ITEMS']], $arSections);
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

        $arItem['LINK'] = preg_replace(
            '/\/$/',
            '_'.$this->arParams['SECTION_APPEND_PATH'].'/',
            $arItem['LINK']
        );

        return $arItem;
    }

}