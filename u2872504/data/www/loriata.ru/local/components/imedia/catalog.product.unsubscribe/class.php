<?php

namespace Imedia\Component;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Catalog\SubscribeProduct;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CatalogProductUnsubscribe extends \CBitrixComponent
{
    protected function checkModules()
    {
        if (!Loader::includeModule('imedia.main')) {
            throw new \Exception(Loc::getMessage('T_IMEDIA_MAIN_MODULE_NOT_INSTALLED'));
        }
    }

    protected function unsubscribeProduct(\Bitrix\Main\HttpRequest $request)
    {
        $getData = $request->getQueryList()->toArray();

        $result = SubscribeProduct::unsubscribe($getData);
        if (!$result->isSuccess()) {
            $errorObject = current($result->getErrors());
            $this->arResult['ERROR'] = $errorObject->getMessage();
        }
    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->checkModules();
            $this->unsubscribeProduct($this->request);
            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            ShowError($e->getMessage());
        }
    }    
}