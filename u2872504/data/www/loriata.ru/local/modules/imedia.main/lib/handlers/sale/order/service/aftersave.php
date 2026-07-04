<?php
namespace Imedia\Main\Handlers\Sale\Order\Service;

use Bitrix\Sale\BusinessValue;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Handlers\Sale\Order as OrderHandler;
use Imedia\Main\Helpers\Debug\Logger;
use Imedia\Main\Models\OrderAutoCancel\OrderAutoCancel;

class AfterSave
{
    protected OrderHandler $handler;
    protected Logger\Logger $logger;

    public function __construct(OrderHandler $handler)
    {
        $this->handler = $handler;

        $this->logger = new Logger\Logger();
        $this->logger->routes->attach(new Logger\Route\File(
            [
                'isEnable' => true,
                'logDir' => 'sale/order/' .str_replace(__NAMESPACE__, '', static::class)
            ]
        ));
    }

    public function process()
    {
        try {

            $this->setAutoCancel();

        } catch (\Exception $e){
            $this->logger->critical($e->getMessage(), [
                'orderId' => $this->handler->order->getId()
            ]);
        }
    }

    protected function setAutoCancel(): void
    {

        if(!$this->handler->isNew){
            return;
        }

        foreach($this->handler->order->getPaymentCollection() as $payment){

            $timeout = (int) BusinessValue::get(
                'AUTO_CANCEL',
                $payment->getPaySystem()->getConsumerName(),
                $this->handler->order->getPersonTypeId()
            );

            if(!($timeout > 0)){
                continue;
            }

            $date = \Bitrix\Main\Type\DateTime::createFromTimestamp(time() + $timeout);

            $obj = new OrderAutoCancel();
            $obj->setOrderId($this->handler->order->getId());
            $obj->setDate($date);
            $obj->setReason(Loc::getMessage('IMEDIA_MAIN_HANDLER_ORDER_AFTER_SAVE_AUTO_CLOSE_REASON_PAYMENT', [
                '#PAY_SYSTEM_ID#' => $payment->getPaymentSystemId(),
                '#PAY_SYSTEM_NAME#' => $payment->getPaymentSystemName(),
                '#PAYMENT_ID#' => $payment->getId()
            ]));

            $saveResult = $obj->save();
            if(!($saveResult->isSuccess())){
                $this->logger->error(implode(', ', $saveResult->getErrorMessages()), [
                    'orderId' => $this->handler->order->getId(),
                    'paySystemId' => $payment->getPaymentSystemId(),
                    'paymentId' => $payment->getId()
                ]);
            }

            break;
        }

    }
}