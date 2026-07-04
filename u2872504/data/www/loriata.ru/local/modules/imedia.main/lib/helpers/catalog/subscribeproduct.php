<?php

namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Localization\Loc;

use Bitrix\Catalog\SubscribeTable;
use Bitrix\Catalog\Product\SubscribeManager;

class SubscribeProduct
{
    public static function get(int $productId, int $userId): Result
    {
        $result = new Result();

        if (!empty($_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID'])) {
            if (array_key_exists($productId, $_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID'])) {
                $data = [
                    'ID' => $productId,
                    'IS_SUBSCRIBED' => true
                ];
            } else {
                $data = static::getStatusSubscribe($productId, $userId);
            }
        } else {
            $data = static::getStatusSubscribe($productId, $userId);
        }

        $result->setData($data);

        return $result;
    }

    public static function getStatusSubscribe(int $productId, int $userId): array
    {
        global $DB;

        Loader::includeModule('catalog');

        $filter = [
            'ITEM_ID' => $productId,
            '=SITE_ID' => SITE_ID,
            [
                'LOGIC' => 'OR',
                ['=DATE_TO' => false],
                ['>DATE_TO' => date($DB->dateFormatToPHP(\CLang::getDateFormat('FULL')), time())]
            ]
        ];
        if ($userId > 0) {
            $filter['USER_ID'] = $userId;
        } else {
            if (!empty($_SESSION['SUBSCRIBE_PRODUCT']['TOKEN']) && !empty($_SESSION['SUBSCRIBE_PRODUCT']['USER_CONTACT'])) {
                $filter['=Bitrix\Catalog\SubscribeAccessTable:SUBSCRIBE.TOKEN'] = $_SESSION['SUBSCRIBE_PRODUCT']['TOKEN'];
                $filter['=Bitrix\Catalog\SubscribeAccessTable:SUBSCRIBE.USER_CONTACT'] = $_SESSION['SUBSCRIBE_PRODUCT']['USER_CONTACT'];
            } else {
                $result = [
                    'ID' => $productId,
                    'IS_SUBSCRIBED' => false
                ];

                return $result;
            }
        }

        $queryObject = SubscribeTable::getList([
            'select' => ['ITEM_ID'],
            'filter' => $filter
        ]);
        $subscribeManager = new SubscribeManager;
        $isSubscribed = false;
        while ($subscribe = $queryObject->fetch()) {
            $subscribeManager->setSessionOfSibscribedProducts($subscribe['ITEM_ID']);

            if($productId === (int) $subscribe['ITEM_ID']){
                $isSubscribed = true;
            }
        }

        $result = [
            'ID' => $productId,
            'IS_SUBSCRIBED' => $isSubscribed
        ];

        return $result;
    }

    public static function subscribe(int $productId, string $email, int $userId): Result
    {
        $result = new Result();

        if (!check_email($email)) {
            $result->addError(new Error(Loc::getMessage('T_IMEDIA_MAIN_HELPERS_CATALOG_SUBSCRIBE_PRODUCT_ERROR_EMAIL')));
            return $result;
        }

        Loader::includeModule('catalog');

        $subscribeManager = new SubscribeManager;

        $subscribeData = [
            'USER_CONTACT' => $email,
            'ITEM_ID' => $productId,
            'SITE_ID' => SITE_ID,
            'CONTACT_TYPE' => SubscribeTable::CONTACT_TYPE_EMAIL,
            'USER_ID' => $userId
        ];

        $subscribeId = $subscribeManager->addSubscribe($subscribeData);

        if (!$subscribeId) {
            $errorObject = current($subscribeManager->getErrors());
            if ($errorObject)
                $result->addError(new Error($errorObject->getMessage()));
        }

        return $result;
    }

    public static function unsubscribe(array $data): Result
    {
        $result = new Result();

        Loader::includeModule('catalog');

        $subscribeManager = new SubscribeManager;

        if (!$subscribeManager->unSubscribe($data)) {
            $errorObject = current($subscribeManager->getErrors());
            if ($errorObject)
                $result->addError(new Error($errorObject->getMessage()));
        }

        return $result;
    }
}
