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

class CatalogMenuAside extends \CBitrixComponent
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
        $this->arResult = $arCatalogMenu[$selectedCatalog];
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
}