<?php

namespace Yandex\Metrika;

use Bitrix\Main\Engine\Controller;


class Ajax extends Controller
{
	public function addEcommerceActionsAction()
    {
        $type = $_POST['type'];

        Ecommerce::registerAction(
            $type,
            [],
            'handler'
        );

        return true;
    }

	public function getEcommerceActionsAction()
	{
		$response = [];

		$actions = Ecommerce::getDBActions();

		$response['actions'] = $actions; //\Bitrix\Main\Web\Json::encode($actions);

		return $response;
	}

	public function removeEcommerceActionsAction()
	{
		$actionsIds = $_POST['actionsIds'];

		if (is_array($actionsIds)) {
			Ecommerce::clearDBActions($actionsIds);
			return true;
		}

		return false;
	}

	public function configureActions()
	{
		return [
            'addEcommerceActions' => [
                '-prefilters' => [
                    \Bitrix\Main\Engine\ActionFilter\Authentication::class,
                ],
            ],
			'getEcommerceActions' => [
				'-prefilters' => [
					\Bitrix\Main\Engine\ActionFilter\Authentication::class,
				],
			],
			'removeEcommerceActions' => [
				'-prefilters' => [
					\Bitrix\Main\Engine\ActionFilter\Authentication::class,
				],
			]
		];
	}
}