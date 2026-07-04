<?php
namespace Imedia\Main\Helpers\Agent\Sale\Order;

use Bitrix\Main\Type;
use Bitrix\Sale\PaySystem\Manager;
use Imedia\Main\Helpers\Agent\Base;
use Imedia\Main\Models\OrderAutoCancel\OrderAutoCancelTable;
use Imedia\Main\Models\OrderAutoCancel\OrderAutoCancel;
use Imedia\Main\Helpers\Sale\Order as OrderHelper;

class AutoClose extends Base
{
    const LIMIT = 30;

    protected function _process()
    {
        $date = new Type\DateTime();

        $query = OrderAutoCancelTable::getList(
            [
                'filter' => [
                    '<=DATE' => $date
                ],
                'limit' => static::LIMIT
            ]
        );
        while($obj = $query->fetchObject()){

            $this->closeOrder($obj);
            $obj->delete();

        }

    }

    protected function closeOrder(OrderAutoCancel $obj): void
    {
        $order = OrderHelper::load($obj->getOrderId());
        if(!$order){

            $this->logger->error('Order not found', [
                'orderId' => $obj->getOrderId(),
                'cancelReason' => $obj->getReason()
            ]);

            return;

        }

        if($order->isPaid()){
            return;
        }

        foreach($order->getPaymentCollection() as $payment){

            if($payment->isPaid()){
                continue;
            }

            [$className, $handlerType] = Manager::includeHandler($payment->getPaySystem()->getField('ACTION_FILE'));
            $handler = new $className($handlerType, $payment->getPaySystem());

            if(method_exists($handler, 'beforeOrderCancel')){
                $handlerResult = $handler->beforeOrderCancel($order, $payment);
                if($handlerResult->isSuccess()){

                    $handlerData = $handlerResult->getData();
                    if($handlerData['PAID']){
                        return;
                    }

                }
            }

        }

        $cancelResult = OrderHelper::cancel($order, null, $obj->getReason());
        if(!($cancelResult->isSuccess())){

            $this->logger->error(implode(', ', $cancelResult->getErrorMessages()), [
                'orderId' => $obj->getOrderId(),
                'cancelReason' => $obj->getReason()
            ]);

        }

    }
}