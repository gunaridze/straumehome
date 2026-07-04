<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery;
use Imedia\Main\Helpers\Sale\Store;

class Order extends Controller
{
    public function configureActions()
    {
        return [
            'sdekPickup' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET]
                    ),
                    new ActionFilter\Csrf()
                ]
            ],
            'pickup' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET]
                    ),
                    new ActionFilter\Csrf()
                ]
            ]
        ];
    }

    public function sdekPickupAction()
    {
        Loader::includeModule('sale');

        return new Response\Component(
            'ipol:ipol.sdekPickup',
            'main',
            [
                'CNT_DELIV' => 'Y',
                'CNT_BASKET' => 'Y'
            ]
        );
    }

    public function pickupAction()
    {
        try{

            Loader::includeModule('sale');

            $arDelivery = Delivery\Services\Table::getList(
                [
                    'select' => ['ID'],
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=XML_ID' => 'PICKUP'
                    ],
                    'limit' => 1
                ]
            )->fetch();

            if(!((int) $arDelivery['ID'] > 0)){
                return [];
            }

            return Store::getListFromDelivery($arDelivery['ID']);

        } catch(\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }
}