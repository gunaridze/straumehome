<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Response;
use Imedia\Main\Helpers\Iblock\Shop;

class Shops extends Controller
{
    public function configureActions()
    {
        return [
            'get' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ]
        ];
    }

    public function getAction()
    {
        try{
            return Shop::getMapData();
        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }
}