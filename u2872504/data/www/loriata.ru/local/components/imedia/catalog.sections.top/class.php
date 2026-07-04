<?php

namespace Imedia\Component;

use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Catalog\Menu;
use Imedia\Main\Helpers\Catalog\Selected;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class CatalogSectionsTop extends \CBitrixComponent
{
    protected function prepareParams()
    {
        if(!((int) $this->arParams['COUNT']) > 0){
            $this->arParams['COUNT'] = 10;
        }

        if(!((int) $this->arParams['SELECTED_CATALOG']) > 0){
            $this->arParams['SELECTED_CATALOG'] = Selected::get();
        }
    }

    protected function getResult()
    {
        $arCatalogMenu = Menu::get();
        $this->arResult = $this->updateMenu($arCatalogMenu[$this->arParams['SELECTED_CATALOG']]);
    }

    protected function updateMenu(array $arSections): array
    {
        $arResult = [];

        $arSections = $this->getMainpage($arSections['ITEMS']);

        if(!empty($arSections)){
            $count = min(count($arSections), $this->arParams['COUNT']);

            $keys = array_rand($arSections, $count);

            foreach($keys as $key){
                $arResult[] = $arSections[$key];
                shuffle($arResult);
            }
        }

        return $arResult;
    }

    protected function getMainpage(array $arSections): array
    {
        $arResult = [];

        foreach($arSections as $arSection){

            if(
                $arSection['MAINPAGE']
                && $arSection['PICTURE']
            ){

                $arAddSection = $arSection;
                if(isset($arAddSection['ITEMS'])){
                    unset($arAddSection['ITEMS']);
                }
                $arResult[] = $arAddSection;

            }

            if(!empty($arSection['ITEMS'])){

                $arAddSections = $this->getMainpage($arSection['ITEMS']);

                foreach($arAddSections as $arAddSection){
                    $arResult[] = $arAddSection;
                }

            }

        }

        return $arResult;
    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->getResult();
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }
}