<?php
namespace Imedia\Main\Handlers\Sale;

use Bitrix\Sale\OrderBase;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Sale\ResultError;
use Bitrix\Main\Error;
use Imedia\Main\Helpers\Location;

class Order
{
    protected array $arFields = [];
    public bool $isNew = false;
    public ?OrderBase $order = null;

    private static ?self $instance;

    private function __construct() {
    }

    private function __clone() {
    }

    private function __wakeup() {
    }

    public static function getInstance(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function onSaleComponentOrderJsData(&$arResult, &$arParams)
    {
        $totalData = [];

        $totalData['weight'] = [
            'raw' => $arResult['JS_DATA']['TOTAL']['ORDER_WEIGHT'],
            'formatted' => $arResult['JS_DATA']['TOTAL']['ORDER_WEIGHT_FORMATED'],
        ];

        $totalData['price'] = [
            'raw' => [
                'base' => $arResult['JS_DATA']['TOTAL']['PRICE_WITHOUT_DISCOUNT_VALUE'],
                'discount' => $arResult['JS_DATA']['TOTAL']['BASKET_PRICE_DISCOUNT_DIFF_VALUE'],
                'delivery' => $arResult['JS_DATA']['TOTAL']['DELIVERY_PRICE'],
                'total' => $arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_PRICE']
            ],
            'formatted' => [
                'base' => $arResult['JS_DATA']['TOTAL']['PRICE_WITHOUT_DISCOUNT'],
                'discount' => $arResult['JS_DATA']['TOTAL']['BASKET_PRICE_DISCOUNT_DIFF'],
                'delivery' => $arResult['JS_DATA']['TOTAL']['DELIVERY_PRICE_FORMATED'],
                'total' => $arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_PRICE_FORMATED']
            ]
        ];

        $arResult['JS_DATA']['TOTAL'] = $totalData;
        $arResult['JS_DATA']['SELECTED_LOCATION'] = Location::getSelected();
        $arResult['JS_DATA']['DISCOUNT_LIST'] = $arResult['DISCOUNT_LIST'];
    }

    public static function onSaleOrderBeforeSaved(Event $event)
    {
        $handler = self::getInstance();
        $handler->order = $event->getParameter('ENTITY');
        $handler->isNew = $event->getParameter('IS_NEW') || ((int) $handler->order->getId() === 0);

        $checkResult = (new Order\Service\BeforeSave($handler))->process();
        if(!($checkResult->isSuccess())){
            return new EventResult(
                EventResult::ERROR,
                ResultError::create(new Error(implode('<br>', $checkResult->getErrorMessages())))
            );
        }
    }

    public static function onSaleOrderSaved(Event $event)
    {
        $handler = self::getInstance();
        $handler->order = $event->getParameter('ENTITY');

        if(!$handler->isNew){
            $handler->isNew = $event->getParameter('IS_NEW');
        }

        (new Order\Service\AfterSave($handler))->process();
    }
}