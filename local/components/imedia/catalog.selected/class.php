<?php

namespace Imedia\Component;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Imedia\Main\Helpers\Catalog\Selected;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class CatalogSelected extends \CBitrixComponent
{

    protected function checkModules()
    {
        if (!Loader::includeModule('imedia.main')) {
            throw new \Exception(Loc::getMessage('IMEDIA_MAIN_MODULE_NOT_INSTALLED'));
        }
    }

    function getResult()
    {
        $selectedId = Selected::get();

        $this->arResult['SECTIONS'] = Selected::getList();
        foreach($this->arResult['SECTIONS'] as $key => $arSection){
            $this->arResult['SECTIONS'][$key]['SELECTED'] = (int) $arSection['ID'] === $selectedId;
            $this->arResult['SECTIONS'][$key]['LINK'] = SITE_DIR . $arSection['CODE'] . '/';
        }
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