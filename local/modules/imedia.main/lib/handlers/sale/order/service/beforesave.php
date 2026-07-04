<?php
namespace Imedia\Main\Handlers\Sale\Order\Service;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\PhoneNumber\Parser;
use Imedia\Main\Handlers\Sale\Order as OrderHandler;
use Imedia\Main\Helpers\Debug\Logger;

class BeforeSave
{
    protected Result $result;
    protected OrderHandler $handler;
    protected Logger\Logger $logger;

    public function __construct(OrderHandler $handler)
    {
        $this->handler = $handler;
        $this->result = new Result();

        $this->logger = new Logger\Logger();
        $this->logger->routes->attach(new Logger\Route\File(
            [
                'isEnable' => true,
                'logDir' => 'sale/order/' .str_replace(__NAMESPACE__, '', static::class)
            ]
        ));
    }

    public function process(): Result
    {
        try {

            $this->checkProperties();
            if(!($this->result->isSuccess())){
                return $this->result;
            }

        } catch (\Exception $e){

            $this->result->addError(new Error($e->getMessage()));

            $this->logger->critical($e->getMessage(), [
                'orderId' => $this->handler->order->getId() ?: 'new'
            ]);
        }

        return $this->result;
    }

    protected function checkProperties(): void
    {
        foreach($this->handler->order->getPropertyCollection() as $property){

            if(!($property->isRequired())){
                continue;
            }

            $propertyConfig = $property->getProperty();
            $value = $property->getValue();

            if($propertyConfig['IS_PHONE'] === 'Y'){

                $parsedPhone = Parser::getInstance()->parse($value);
                if(!($parsedPhone->isValid())){
                    $this->result->addError(
                        new Error(Loc::getMessage('IMEDIA_MAIN_HANDLER_ORDER_BEFORE_SAVE_ERROR_PHONE'))
                    );
                }

                continue;

            }

        }
    }
}