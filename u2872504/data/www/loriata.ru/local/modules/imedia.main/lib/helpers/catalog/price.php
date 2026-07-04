<?php
namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Catalog\GroupTable;
use Imedia\Main\Helpers\Catalog\Price as PriceHelper;

class Price
{
    public const GROUP_BASE = '1b2029a2-dfe2-11ec-9f3d-5e89ca8da90c';
    public const GROUP_SALE = 'c3efe7d0-ddc6-11ec-9f3d-5e89ca8da90c';
    public const GROUP_DISCOUNT = 'DISCOUNT';
    public const CACHE_TAG = 'price-group';
    private const CACHE_ID = 'price-group';
    private const CACHE_TTL = 864000;
    private const CACHE_DIR = '/catalog';

    protected static ?array $list = null;

    public static function getList(): array
    {
        if(gettype(static::$list) === 'NULL'){

            $list = [];

            $cache = Cache::createInstance();

            if ($cache->initCache(static::CACHE_TTL, static::CACHE_ID, static::CACHE_DIR)) {
                $list = $cache->getVars();
            } elseif ($cache->startDataCache()) {

                Loader::includeModule('catalog');

                $query = GroupTable::getList(
                    [
                        'select' => ['ID', 'NAME', 'SORT', 'XML_ID' , 'BASE']
                    ]
                );
                while($row = $query->fetch()){
                    $row['BASE'] = $row['BASE'] === 'Y';
                    $list[] = $row;
                }

                $taggedCache = Application::getInstance()->getTaggedCache();
                $taggedCache->startTagCache(static::CACHE_DIR);
                $taggedCache->registerTag(static::CACHE_TAG);
                $taggedCache->endTagCache();
                $cache->endDataCache($list);

            }

            static::$list = $list;

        }

        return static::$list;
    }

    public static function getId(string $xmlId): int
    {
        $arGroup = static::getGroup($xmlId);
        return (int) $arGroup['ID'];
    }

    public static function getName(string $xmlId): string
    {
        $arGroup = static::getGroup($xmlId);
        return (string) $arGroup['NAME'];
    }

    public static function getGroup(string $xmlId): array
    {
        $list = static::getList();

        foreach($list as $arGroup){

            if(!$arGroup['XML_ID']){
                continue;
            }

            if($arGroup['XML_ID'] === $xmlId){
                return $arGroup;
            }
        }

        return [];
    }

    public static function getOptimal(int $productId, array $arBasePrice = null): array
    {
        Loader::includeModule('catalog');

        $arPrice = \CCatalogProduct::GetOptimalPrice($productId);

        if(!$arPrice){
            return [];
        }

        $arPrice = static::updateOptimalPrice($arPrice, $arBasePrice);

        $arOptimalPrice = [
            'UNROUND_BASE_PRICE' => $arPrice['RESULT_PRICE']['UNROUND_BASE_PRICE'],
            'UNROUND_PRICE' => $arPrice['RESULT_PRICE']['UNROUND_DISCOUNT_PRICE'],
            'BASE_PRICE' => $arPrice['RESULT_PRICE']['BASE_PRICE'],
            'PRICE' => $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'],
            'ID' => $arPrice['RESULT_PRICE']['ID'],
            'PRICE_TYPE_ID' => $arPrice['RESULT_PRICE']['PRICE_TYPE_ID'],
            'CURRENCY' => $arPrice['RESULT_PRICE']['CURRENCY'],
            'DISCOUNT' => $arPrice['RESULT_PRICE']['DISCOUNT'],
            'PERCENT' => round($arPrice['RESULT_PRICE']['PERCENT'])
        ];

        $arFields = [
            'BASE_PRICE',
            'PRICE',
            'DISCOUNT'
        ];

        foreach($arFields as $code){
            $arOptimalPrice['PRINT_' . $code] = \CCurrencyLang::CurrencyFormat(
                $arOptimalPrice[$code],
                $arOptimalPrice['CURRENCY']
            );
        }

        return $arOptimalPrice;
    }

    protected static function updateOptimalPrice(array $arPrice = [], array $arBasePrice = null)
    {
        $priceTypeBase = PriceHelper::getId(PriceHelper::GROUP_BASE);
        if((int) $arPrice['RESULT_PRICE']['PRICE_TYPE_ID'] === $priceTypeBase){
            return $arPrice;
        }

        if(!$arBasePrice){
            $arBasePrice = PriceTable::getList(
                [
                    'select' => ['PRICE_SCALE'],
                    'filter' => [
                        '=PRODUCT_ID' => $arPrice['PRODUCT_ID'],
                        '=CATALOG_GROUP_ID' => $priceTypeBase
                    ],
                    'limit' => 1
                ]
            )->fetch();
        }

        if(!$arBasePrice){
            return $arPrice;
        }

        $basePrice = round($arBasePrice['PRICE_SCALE'], 2);

        if(!($basePrice > $arPrice['RESULT_PRICE']['BASE_PRICE'])){
            return $arPrice;
        }

        $arPrice['RESULT_PRICE']['BASE_PRICE']
            = $arPrice['RESULT_PRICE']['UNROUND_BASE_PRICE']
            = $basePrice;

        $arPrice['RESULT_PRICE']['DISCOUNT']
            = $arPrice['RESULT_PRICE']['BASE_PRICE'] - $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'];

        $arPrice['RESULT_PRICE']['PERCENT'] = 0;
        if($arPrice['RESULT_PRICE']['BASE_PRICE'] > 0){
            $arPrice['RESULT_PRICE']['PERCENT']
                = round($arPrice['RESULT_PRICE']['DISCOUNT'] / ($arPrice['RESULT_PRICE']['BASE_PRICE'] * 0.01));
        }

        return $arPrice;
    }
}