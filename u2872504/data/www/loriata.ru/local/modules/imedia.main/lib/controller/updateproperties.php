<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Engine\Response;
use Imedia\Main\Helpers\ActionFilter as CustomActionFilter;
use Imedia\Main\Helpers\Catalog\Service\UpdateProperties as UpdatePropertiesService;

class UpdateProperties extends Controller
{
    public function configureActions(): array
    {
        return [
            'process' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                    new ActionFilter\Authentication(),
                    new CustomActionFilter\Rights(
                        [
                            'groups' => ['ADMIN']
                        ]
                    )
                ],
            ]
        ];
    }

    public function processAction()
    {
        try {

            $result = UpdatePropertiesService::process();

            $data = $result->getData();

            return [
                'total' => (int) $data['TOTAL']
            ];

        } catch (\Exception $e){

            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );

        }
    }
}