<?php
namespace Imedia\Main\Helpers\Iblock\Seo\Handler\Section;

use Bitrix\Iblock\PropertyIndex\Facet as FacetIndex;
use Bitrix\Iblock\PropertyIndex\Storage;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Catalog\GroupTable;
use Bitrix\Currency\CurrencyManager;
use Imedia\Main\Helpers\Iblock\Seo\Handler\Base;
use Imedia\Main\Helpers\Catalog\Price;

class MinPrice extends Base
{
    const TEMPLATE = '{minPrice}';

    protected function canHandle(string $string): bool
    {
        return strpos($string, static::TEMPLATE) !== false;
    }

    protected function handle(string $string, array $data): string
    {
        $result = $this->getResult($data);
        $string = str_replace(static::TEMPLATE, $result, $string);

        return $string;
    }

    protected function getResult(array $data): string
    {
        $result = '';

        if(!($data['ID'] > 0)){
            return $result;
        }

        Loader::includeModule('iblock');
        Loader::includeModule('catalog');

        $facet = new FacetIndex($data['IBLOCK_ID']);
        $storage = $facet->getStorage();
        $arPrice = GroupTable::getList(
            [
                'select' => ['ID'],
                'filter' => [
                    '=NAME' => Price::GROUP_DISCOUNT
                ],
                'limit' => 1
            ]
        )->fetch();

        if(!$arPrice){
            return $result;
        }

        $facetId = Storage::priceIdToFacetId($arPrice['ID']);
        $connection = Application::getConnection();
        $arValue = $connection
            ->query("SELECT MIN(VALUE_NUM) AS MIN_PRICE FROM ".$storage->getTableName()." WHERE FACET_ID = " . $facetId ." AND SECTION_ID =" . $data['ID'], 1)
            ->fetch()
        ;

        if(!((float) $arValue['MIN_PRICE'] > 0)){
            return $result;
        }

        Loader::includeModule('currency');

        $result = \CCurrencyLang::CurrencyFormat(
            $arValue['MIN_PRICE'],
            CurrencyManager::getBaseCurrency()
        );

        return $result;
    }
}