<?php
namespace Ipolh\SDEK\Bitrix\Handler;

use Ipolh\SDEK\Bitrix\Tools;

class goodsPicker
{
    /**
     * Add marking codes to basket goods data
     * @param array $arGoods array of basket goods
     * @param int $orderId Bitrix order id
     */
    public static function addGoodsQRs(&$arGoods, $bitrixId)
    {
        if (Tools::isConverted()) {
            $isMarkingAvailable = method_exists('\\Bitrix\\Sale\\ShipmentItemStore', 'getMarkingCode');
            $order = \Bitrix\Sale\Order::load($bitrixId);

            $shipments = $order->getShipmentCollection();
            foreach ($shipments as $shipment) {
                $items = $shipment->getShipmentItemCollection();
                foreach ($items as $item) {
                    /** @var \Bitrix\Sale\BasketItem $basketItem */
                    $basketItem = $item->getBasketItem();
                    $stores     = $item->getShipmentItemStoreCollection();
                    foreach ($stores as $store) {
                        /** @var \Bitrix\Sale\ShipmentItemStore $store */
                        $mark = ($isMarkingAvailable) ? $store->getMarkingCode() : '';

                        foreach ($arGoods as $key => $stuff) {
                            if ((int)$arGoods[$key]['PRODUCT_ID'] === $basketItem->getProductId()) {
                                if (!array_key_exists('QR', $arGoods[$key])) {
                                    $arGoods[$key]['QR'] = array();
                                }
                                $arGoods[$key]['QR'] [] = $mark;
                            }
                        }
                    }
                }
            }
        }
    }
}