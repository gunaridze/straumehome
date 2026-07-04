<?php

namespace Imedia\Component;

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class HtmlInclude extends \CBitrixComponent
{
    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }
}