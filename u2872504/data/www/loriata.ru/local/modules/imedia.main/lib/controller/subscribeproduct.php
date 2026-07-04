<?php

namespace Imedia\Main\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response;

use Imedia\Main\Helpers\Catalog\SubscribeProduct as SubscribeProductHelper;

class SubscribeProduct extends Controller
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
            ],
            'subscribe' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ]
            ]
        ];
    }

    public function getAction(int $productId, CurrentUser $currentUser)
    {
        try {
            $result = SubscribeProductHelper::get($productId, (int) $currentUser->getId());

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

    public function subscribeAction(int $productId, string $email, CurrentUser $currentUser)
    {
        try {
            $result = SubscribeProductHelper::subscribe($productId, $email, (int) $currentUser->getId());

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

