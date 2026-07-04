<?php

namespace Imedia\Main\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response;

use Imedia\Main\Helpers\Catalog\SizeTable as SizeTableHelper;

class SizeTable extends Controller
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

    public function getAction(int $productId, int $elementId = null, array $sectionIds = null)
    {
        try {
            $result = SizeTableHelper::get($productId, $elementId, $sectionIds);

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
