<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Response;
use Imedia\Main\Helpers\Location as LocationHelper;

class Location extends Controller
{
    public function configureActions()
    {
        return [
            'search' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf()
                ]
            ],
            'set' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf()
                ]
            ]
        ];
    }

    public function searchAction(string $query, string $type)
    {
        $query = trim(filter_var(
            $query,
            FILTER_SANITIZE_STRING
        ));

        if(!$query || (strlen($query) < 3)){
            return [];
        }

        try{

            $items = LocationHelper::search($query, $type);

            return $items;

        } catch(\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function setAction(string $locationCode)
    {
        try {

            $arLocation = LocationHelper::getLocation($locationCode);
            if($arLocation){
                LocationHelper::setLocation($arLocation);
            }

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }
}