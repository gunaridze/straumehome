<?php

namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Response;
use Imedia\Main\Helpers\Catalog\DefferedProduct as DefferedProductHelper;

class DefferedProduct extends Controller
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
            ],
            'add' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'remove' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ]
        ];
    }

    public function addAction(int $productId, string $type)
    {
        try{
           $result = DefferedProductHelper::add($productId, $type);

            if(!$result->isSuccess()){
                return Response\AjaxJson::createError( $result->getErrorCollection() );
            }

            return $result->getData();
        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function removeAction(int $productId, string $type)
    {
        try{
            $result = DefferedProductHelper::remove($productId, $type);

            if(!$result->isSuccess()){
                return Response\AjaxJson::createError( $result->getErrorCollection() );
            }

            return $result->getData();
        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function getAction()
    {
        try{
            $result = DefferedProductHelper::getAll();

            if(!$result->isSuccess()){
                return Response\AjaxJson::createError( $result->getErrorCollection() );
            }

            return $result->getData();
        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }
}