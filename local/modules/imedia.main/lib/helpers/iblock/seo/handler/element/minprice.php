<?php
namespace Imedia\Main\Helpers\Iblock\Seo\Handler\Element;

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

        if($data['PRICE_MIN']){
            $result = $data['PRICE_MIN'];
        }

        return $result;
    }
}