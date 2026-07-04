<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Engine\Response;
use Imedia\Main\Helpers\ActionFilter as CustomActionFilter;
use Imedia\Main\Helpers\Catalog\Service\UpdatePrices as UpdatePricesService;

class UpdatePrices extends Controller
{
    public function configureActions(): array
    {
        return [
            'run' => [
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
            ],
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
            ],
        ];
    }

    public function runAction()
    {
        try{

            UpdatePricesService::run();

            return [
                'types' => UpdatePricesService::getTypesQueue(),
                'inProgress' => Option::get(
                        UpdatePricesService::MODULE_ID,
                        UpdatePricesService::OPTION_PREFIX . 'in_progress',
                        'N'
                    ) === 'Y',
                'currentType' => Option::get(
                    UpdatePricesService::MODULE_ID,
                    UpdatePricesService::OPTION_PREFIX . 'current_type'
                )
            ];

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function processAction()
    {
        try{

            UpdatePricesService::process();

            return [
                'inProgress' => Option::get(
                    UpdatePricesService::MODULE_ID,
                    UpdatePricesService::OPTION_PREFIX . 'in_progress',
                    'N'
                ) === 'Y',
                'currentType' => Option::get(
                    UpdatePricesService::MODULE_ID,
                    UpdatePricesService::OPTION_PREFIX . 'current_type'
                )
            ];

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }
}