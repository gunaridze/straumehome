<?php

namespace Imedia\Component;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class CatalogItem extends \CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        if (!empty($params['PARAMS']))
        {
            $params += $params['PARAMS'];
            unset($params['PARAMS']);
        }

        return $params;
    }

    function getResult()
    {
        $this->arResult = $this->arParams['ITEM'];
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