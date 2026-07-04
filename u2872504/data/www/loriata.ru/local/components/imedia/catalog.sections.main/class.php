<?php

namespace Imedia\Component;

use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Catalog\Menu;
use Imedia\Main\Helpers\Catalog\Selected;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class CatalogSectionsMain extends \CBitrixComponent
{
    protected function getResult()
    {
        $arCatalogMenu = Menu::get();

        $this->arResult = [];
        foreach($arCatalogMenu as $arSection){
            if(isset($arSection['ITEMS'])){
                unset($arSection['ITEMS']);
            }

            $arSection['LINK'] = str_replace($this->arParams['CATALOG_LINK'], '', $arSection['LINK']);

            $this->arResult[] = $arSection;
        }

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