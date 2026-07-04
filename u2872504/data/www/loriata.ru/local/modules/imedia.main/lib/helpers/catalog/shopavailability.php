<?php

namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Main\Result;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Loader;

use Imedia\Main\Helpers\Iblock\Shop;

class ShopAvailability
{
    /**
     * @param int $productId
     * @return Result
     */
    public static function get(int $productId): Result
    {
        $result = new Result();

        Loader::includeModule('catalog');

        $storeList = self::getStoresByProductId($productId);
        if (empty($storeList))
            return $result;

        $shopList = Shop::getList();
        if (empty($shopList))
            return $result;

        foreach ($shopList['ITEMS'] as $arShopItem) {
            if (!empty($storeList) && array_key_exists($arShopItem['STORE_ID'], $storeList)) {
                $arShops['ITEMS'][] = $arShopItem;
                $sectionIds[$arShopItem['IBLOCK_SECTION_ID']] = true;
            }
        }

        foreach ($shopList['SECTIONS'] as $arShopSectionItem) {
            if (!empty($sectionIds) && array_key_exists($arShopSectionItem['ID'], $sectionIds)) {
                $arShops['SECTIONS'][] = $arShopSectionItem;
            }
        }

        if (!empty($arShops))
            $result->setData($arShops);

        return $result;
    }

    public static function getStoresByProductId(int $productId): array
    {
        $result = [];

        $rsStores = StoreProductTable::getList([
            'select' => [
                'STORE_ID'
            ],
            'filter' => [
                '=PRODUCT_ID' => $productId,
                '=STORE.ACTIVE' => 'Y',
                '>AMOUNT' => 0
            ]
        ]);
        while ($arStore = $rsStores->fetch()) {
            $result[$arStore['STORE_ID']] = true;
        }

        return $result;
    }
}
