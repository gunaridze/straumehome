<?php
namespace Imedia\Main\Helpers\Sale;

use Bitrix\Main\Loader;
use Bitrix\Sale\Registry;
use Bitrix\Sale\OrderBase;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Sale;
use Bitrix\Sale\Payment;

class Order
{
    const STATUS_NEW = 'N'; // Новый
    const STATUS_SUCCESS = 'F'; // Выполнен

    public static function load(int $orderId)
    {
        Loader::includeModule('sale');

        $registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
        $orderClass = $registry->getOrderClassName();

        return $orderClass::load($orderId);
    }

    public static function getPropertyByCode(string $propertyCode, $propertyCollection)
    {
        $properties = $propertyCollection->getArray();
        foreach($properties['properties'] as $arProperty){
            if($arProperty['CODE'] === $propertyCode){
                return $propertyCollection->getItemByOrderPropertyId($arProperty['ID']);
            }
        }

        return null;
    }

    /**
     * @param OrderBase $order
     * @param string|null $statusId
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     */
    public static function cancel(OrderBase $order, string $statusId = null, string $reason = null): Result
    {
        $result = new Result();

        if(!$order->isCanceled()){

            $paymentCollection = $order->getPaymentCollection();
            foreach($paymentCollection as $payment){
                if($payment->isPaid()){

                    if($payment->getPaySystem()->isRefundable()){
                        $refundResult = $payment->setReturn(Payment::RETURN_PS);
                        if(!$refundResult->isSuccess()){
                            foreach($refundResult->getErrorMessages() as $errorMessage){
                                $result->addError( new Error( $errorMessage ) );
                            }

                            return $result;
                        }
                    }

                    $payment->setPaid('N');
                } else if ($payment->getPaySystem()->isBlockable()){
                    $payment->getPaySystem()->cancel($payment);
                }
            }

            $shipmentCollection = $order->getShipmentCollection()->getNotSystemItems();
            foreach($shipmentCollection as $shipment){

                if ($shipment->isShipped()){
                    $shipment->setField('DEDUCTED', 'N');
                }

                if ($shipment->isAllowDelivery()){
                    $shipment->disallowDelivery();
                }

            }

            $order->setField('CANCELED', 'Y');

            if($statusId){
                $order->setField('STATUS_ID', $statusId);
            }

            if($reason){
                $order->setField('REASON_CANCELED', $reason);
            }

            $result = $order->save();
            Sale\Provider::resetTrustData($order->getSiteId());
        }

        return $result;
    }
}