<?php
namespace Imedia\Component;

use Bitrix\Main\Loader;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

\CBitrixComponent::includeComponentClass('bitrix:catalog.smart.filter');
class CatalogSmartFilter extends \CBitrixCatalogSmartFilter
{
    public function replaceUrl(string $url): string
    {
        if(!empty($this->arParams['SMART_FILTER_URL_REPLACE'])){
            $url = str_replace(
                $this->arParams['SMART_FILTER_URL_REPLACE']['FROM'],
                $this->arParams['SMART_FILTER_URL_REPLACE']['TO'],
                $url
            );
        }

        return $url;
    }
}