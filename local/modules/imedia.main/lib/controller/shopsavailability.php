<?php

namespace Imedia\Main\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response;

use Imedia\Main\Helpers\Catalog\ShopAvailability;

class ShopsAvailability extends Controller
{
    public function configureActions(): array
    {
        return [
            'get' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET]
                    ),
                    new ActionFilter\Csrf(),
                ]
            ]
        ];
    }

    public function getAction(int $productId)
    {
        try {
            $result = ShopAvailability::get($productId);

            if (!$result->isSuccess()) {
                return Response\AjaxJson::createError($result->getErrorCollection());
            }

            return $result->getData();
        } catch (\Exception $e) {
            $result = new Result();
            $result->addError(new Error($e->getMessage()));
            return Response\AjaxJson::createError($result->getErrorCollection());
        }
    }
}
