<?php
namespace Imedia\Main\Helpers\Sale;

use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery\ExtraServices\Manager;
use Bitrix\Catalog\StoreTable;

class Store
{
    public static function getListFromDelivery(int $deliveryId, string $locationCode = null): array
    {
        Loader::includeModule('sale');

        $arStores = [];

        $storesIds = Manager::getStoresList($deliveryId);

        $filter = [
            '=ID' => $storesIds,
            '=ACTIVE' => 'Y'
        ];

        if($locationCode){
            $filter['=UF_LOCATION'] = $locationCode;
        }

        $query = StoreTable::getList(
            [
                'select' => ['*', 'UF_*'],
                'filter' => $filter
            ]
        );
        while($row = $query->fetch()){
            $arStores[] = $row;
        }

        return $arStores;
    }
}