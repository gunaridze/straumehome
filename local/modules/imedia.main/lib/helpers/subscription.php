<?php
namespace Imedia\Main\Helpers;

use Bitrix\Main\Application;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\DiscountTable;
use Bitrix\Sale\Internals\DiscountCouponTable;
use Imedia\Main\Helpers\Iblock\Info;
use Imedia\Main\Models\CouponEmail\CouponEmail;
use Imedia\Main\Models\CouponEmail\CouponEmailTable;

class Subscription
{
    const CODE_MAIN = 'MAIN';
    const DISCOUNT_CODE = 'COUPON_FOR_SUBSCRIBE';
    const EVENT_SUBSCRIBE = 'USER_SUBSCRIBE';
    const EVENT_UNSUBSCRIBE = 'USER_UNSUBSCRIBE';

    public static function subscribeFooter(string $email, CurrentUser $currentUser = null): Result
    {
        $result = new Result();

        if(!$currentUser){
            $currentUser = CurrentUser::get();
        }

        if(!check_email($email)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_EMAIL')));
            return $result;
        }

        if(!Loader::includeModule('subscribe')){
            $result->addError(new Error(Loc::getMessage(
                'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_MODULE',
                ['#VALUE#' => 'subscribe']
            )));
            return $result;
        }

        $arFilter = [
            'LID' => SITE_ID,
            'ACTIVE' => 'Y',
            'CODE' => static::CODE_MAIN
        ];

        $arRubric = \CRubric::GetList([], $arFilter)->GetNext(true, false);
        if(!$arRubric){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIPTION')));
            return $result;
        }

        $subscription = new \CSubscription;

        $id = $subscription->Add(
            [
                'EMAIL' => $email,
                'USER_ID' => $currentUser->getId(),
                'RUB_ID' => $arRubric['ID'],
                'SEND_CONFIRM' => 'N',
                'FORMAT' => 'html',
                'CONFIRMED' => 'Y'
            ]
        );

        if(!($id > 0)){
            $result->addError(
                new Error(
                    Loc::getMessage(
                        'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIBE',
                        ['#ERROR#' => $subscription->LAST_ERROR]
                    )
                )
            );

            return $result;
        }

        $arEmailData = static::getEmailData($id, $arRubric['ID']);
        static::sendEvent(static::EVENT_SUBSCRIBE, $arEmailData);

        return $result;
    }

    public static function getList(CurrentUser $currentUser = null): Result
    {
        $result = new Result();

        if(!$currentUser){
            $currentUser = CurrentUser::get();
        }

        if(!Loader::includeModule('subscribe')){
            $result->addError(new Error(Loc::getMessage(
                'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_MODULE',
                ['#VALUE#' => 'subscribe']
            )));
            return $result;
        }

        $subscriptionId = static::getSubscriptionIdByUser($currentUser);
        if(!($subscriptionId > 0)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIBE_CREATE')));
            return $result;
        }

        $rubrics = static::getRubrics();
        $userSubscriptions = static::getUserSubscriptions($currentUser);

        $list = [];

        foreach($rubrics as $rubric){

            $haveCurrent = false;

            foreach($userSubscriptions as $id => $arItem){

                if(!in_array($rubric['id'], $arItem['rubrics'])){
                    continue;
                }

                if($subscriptionId === (int) $id){
                    $haveCurrent = true;
                }

                $list[] = [
                    'key' => $rubric['id'] . '_' . $id,
                    'rubric' => $rubric,
                    'userData' => $arItem['userData'],
                    'subscriptionId' => $id,
                    'isSubscribed' => true
                ];

            }

            if(!$haveCurrent){

                $list[] = [
                    'key' => $rubric['id'] . '_' . $subscriptionId,
                    'rubric' => $rubric,
                    'userData' => [
                        'email' => $currentUser->getEmail(),
                        'id' => (int) $currentUser->getId(),
                        'isAnonymous' => false,
                        'isCurrent' => true
                    ],
                    'subscriptionId' => $subscriptionId,
                    'isSubscribed' => false
                ];

            }

        }

        $result->setData($list);

        return $result;
    }

    protected static function getRubrics(): array
    {
        $arSort = [
            'SORT' => 'ASC',
            'NAME' => 'ASC'
        ];

        $arFilter = [
            '=ACTIVE' => 'Y',
            '=LID' => SITE_ID,
            '=VISIBLE' => 'Y'
        ];

        $list = [];

        $query = \CRubric::GetList($arSort, $arFilter);
        while($row = $query->GetNext(true, false)){

            $list[] = [
                'id' => (int) $row['ID'],
                'name' => $row['NAME'],
                'description' => $row['DESCRIPTION'],
                'type' => 'email'
            ];

        }

        return $list;
    }

    protected static function getUserSubscriptions(CurrentUser $currentUser): array
    {
        $list = [];

        $userId = (int) $currentUser->getId();
        $userEmail = $currentUser->getEmail();

        $arFilter = [
            'EMAIL' => $userEmail
        ];

        $query = \CSubscription::GetList([], $arFilter);
        while($row = $query->GetNext(true, false)){

            $list[$row['ID']] = [
                'userData' => [
                    'email' => $row['EMAIL'],
                    'id' => (int) $row['USER_ID'],
                    'isAnonymous' => !$row['USER_ID'],
                    'isCurrent' => $userId === (int) $row['USER_ID']
                ]
            ];
        }

        $arFilter = [
            'USER_ID' => $userId
        ];

        $query = \CSubscription::GetList([], $arFilter);
        while($row = $query->GetNext(true, false)){

            if(isset($list[$row['ID']])){
                continue;
            }

            $list[$row['ID']] = [
                'userData' => [
                    'email' => $row['EMAIL'],
                    'id' => (int) $row['USER_ID'],
                    'isAnonymous' => !$row['USER_ID'],
                    'isCurrent' => $userId === (int) $row['USER_ID']
                ]
            ];
        }

        foreach($list as $id => $arItem){
            $list[$id]['rubrics'] = \CSubscription::GetRubricArray($id);
        }

        return $list;
    }

    protected static function getSubscriptionIdByUser(CurrentUser $currentUser = null): int
    {
        if(!$currentUser){
            $currentUser = CurrentUser::get();
        }

        $arFilter = [
            'USER_ID' => $currentUser->getId()
        ];

        $query = \CSubscription::GetList([], $arFilter);
        while($row = $query->GetNext(true, false)){

            if($currentUser->getEmail() === $row['EMAIL']){
                return (int) $row['ID'];
            }

        }

        $subscription = new \CSubscription;

        $id = $subscription->Add(
            [
                'EMAIL' => $currentUser->getEmail(),
                'USER_ID' => $currentUser->getId(),
                'SEND_CONFIRM' => 'N',
                'FORMAT' => 'html',
                'CONFIRMED' => 'Y'
            ]
        );

        return (int) $id;
    }

    public static function subscribe(int $rubricId, CurrentUser $currentUser = null): Result
    {
        $result = new Result();

        if(!$currentUser){
            $currentUser = CurrentUser::get();
        }

        if(!((int) $currentUser->getId() > 0)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_ACCESS')));
            return $result;
        }

        $email = $currentUser->getEmail();

        if(!check_email($email)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_EMAIL')));
            return $result;
        }

        if(!Loader::includeModule('subscribe')){
            $result->addError(new Error(Loc::getMessage(
                'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_MODULE',
                ['#VALUE#' => 'subscribe']
            )));
            return $result;
        }

        $subscriptionId = static::getSubscriptionIdByUser($currentUser);
        if(!($subscriptionId > 0)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIBE_CREATE')));
            return $result;
        }

        $rubrics = \CSubscription::GetRubricArray($subscriptionId);
        if(in_array($rubricId, $rubrics)){
            return $result;
        }

        $rubrics[] = $rubricId;

        $subscription = new \CSubscription;

        $subscription->Update(
            $subscriptionId,
            [
                'RUB_ID' => $rubrics
            ]
        );

        if($subscription->LAST_ERROR){
            $result->addError(
                new Error(
                    Loc::getMessage(
                        'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIBE',
                        ['#ERROR#' => $subscription->LAST_ERROR]
                    )
                )
            );

            return $result;
        }

        $arEmailData = static::getEmailData($subscriptionId, $rubricId);
        static::sendEvent(static::EVENT_SUBSCRIBE, $arEmailData);

        return $result;
    }

    public static function unsubscribe(int $rubricId, int $subscriptionId, CurrentUser $currentUser = null): Result
    {
        $result = new Result();

        if(!$currentUser){
            $currentUser = CurrentUser::get();
        }

        if(!((int) $currentUser->getId() > 0)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_ACCESS')));
            return $result;
        }

        if(!Loader::includeModule('subscribe')){
            $result->addError(new Error(Loc::getMessage(
                'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_MODULE',
                ['#VALUE#' => 'subscribe']
            )));
            return $result;
        }

        $arSubscription = \CSubscription::GetByID($subscriptionId)->GetNext(true, false);
        if(!(
            ($arSubscription['EMAIL'] === $currentUser->getEmail())
            || ((int) $arSubscription['USER_ID'] === (int) $currentUser->getId())
        )){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_ACCESS')));
            return $result;
        }

        $rubrics = \CSubscription::GetRubricArray($subscriptionId);
        if(!in_array($rubricId, $rubrics)){
            return $result;
        }

        $rubrics = array_diff($rubrics, [$rubricId]);

        $arEmailData = static::getEmailData($subscriptionId, $rubricId);

        $subscription = new \CSubscription;

        $subscription->Update(
            $subscriptionId,
            [
                'RUB_ID' => array_values($rubrics)
            ]
        );

        if($subscription->LAST_ERROR){
            $result->addError(
                new Error(
                    Loc::getMessage(
                        'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIBE',
                        ['#ERROR#' => $subscription->LAST_ERROR]
                    )
                )
            );

            return $result;
        }

        static::sendEvent(static::EVENT_UNSUBSCRIBE, $arEmailData);

        return $result;
    }

    public static function couponForSubscribe(string $email, CurrentUser $currentUser = null): Result
    {
        $result = new Result();

        if(!check_email($email)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_EMAIL')));
            return $result;
        }

        if(!$currentUser){
            $currentUser = CurrentUser::get();
        }

        $arCouponEmail = CouponEmailTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['=EMAIL' => $email],
                'limit' => 1
            ]
        )->fetch();
        if($arCouponEmail){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_COUPON')));
            return $result;
        }

        if(!Loader::includeModule('subscribe')){
            $result->addError(new Error(Loc::getMessage(
                'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_MODULE',
                ['#VALUE#' => 'subscribe']
            )));
            return $result;
        }

        $arFilter = [
            'LID' => SITE_ID,
            'ACTIVE' => 'Y',
            'CODE' => static::CODE_MAIN
        ];

        $arRubric = \CRubric::GetList([], $arFilter)->GetNext(true, false);
        if(!$arRubric){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIPTION')));
            return $result;
        }

        $subscription = new \CSubscription;

        $id = $subscription->Add(
            [
                'EMAIL' => $email,
                'USER_ID' => $currentUser->getId(),
                'RUB_ID' => $arRubric['ID'],
                'SEND_CONFIRM' => 'N',
                'FORMAT' => 'html',
                'CONFIRMED' => 'Y'
            ]
        );

        if(!($id > 0)){
            $result->addError(
                new Error(
                    Loc::getMessage(
                        'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIBE',
                        ['#ERROR#' => $subscription->LAST_ERROR]
                    )
                )
            );
            return $result;
        }

        $arEmailData = static::getEmailData($id, $arRubric['ID']);
        static::sendEvent(static::EVENT_SUBSCRIBE, $arEmailData);

        Loader::includeModule('sale');

        $arDiscount = DiscountTable::getList(
            [
                'select' => ['ID'],
                'filter' => [
                    '=XML_ID' => static::DISCOUNT_CODE,
                    '=ACTIVE' => true
                ],
                'limit' => 1
            ]
        )->fetch();

        if(!$arDiscount){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_DISCOUNT')));
            return $result;
        }

        $coupon = DiscountCouponTable::generateCoupon(true);

        $arFields = [
            'DISCOUNT_ID' => $arDiscount['ID'],
            'COUPON'      => $coupon,
            'TYPE'        => DiscountCouponTable::TYPE_ONE_ORDER,
            'MAX_USE'     => 1
        ];

        if($currentUser->getId() > 0){
            $arFields['USER_ID'] = $currentUser->getId();
        }

        $addResult = DiscountCouponTable::add($arFields);
        if(!($addResult->isSuccess())){
            $result->addErrors($addResult->getErrors());
        }

        $couponEmail = new CouponEmail();
        $couponEmail->setEmail($email);
        $couponEmail->setCouponId($addResult->getId());
        $couponEmail->save();

        $event = new \CEvent;
        $event->Send('COUPON_FOR_SUBSCRIBE', SITE_ID, [
            'EMAIL' => $email,
            'COUPON' => $coupon
        ]);

        return $result;
    }

    public static function couponForSubscribeCheck(CurrentUser $currentUser = null): Result
    {
        $result = new Result();


        if(!$currentUser){
            $currentUser = CurrentUser::get();
        }

        if(!($currentUser->getId()) > 0){
            return $result;
        }

        if(!$currentUser->getEmail()){
            return $result;
        }

        if(!Loader::includeModule('subscribe')){
            $result->addError(new Error(Loc::getMessage(
                'IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_MODULE',
                ['#VALUE#' => 'subscribe']
            )));
            return $result;
        }

        $arFilter = [
            'LID' => SITE_ID,
            'ACTIVE' => 'Y',
            'CODE' => static::CODE_MAIN
        ];

        $arSubscription = \CRubric::GetList([], $arFilter)->GetNext(true, false);
        if(!$arSubscription){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_SUBSCRIPTION')));
            return $result;
        }

        $arFilter = [
            'USER' => $currentUser->getId(),
            'RUBRIC' => [$arSubscription['ID']]
        ];

        $arUserSubscription = \CSubscription::GetList([], $arFilter)->Fetch();
        if($arUserSubscription){
            $result->addError(new Error(Loc::getMessage('IMEDIA_MAIN_HELPER_SUBSCRIPTION_ERROR_COUPON')));
            return $result;
        }

        return $result;
    }

    public static function getEmailData(int $subscriptionId, int $rubricId): array
    {
        $data = [];

        Loader::includeModule('subscribe');

        $arSubscription = \CSubscription::GetByID($subscriptionId)->GetNext(true, false);

        $arSubscriptionFields = ['EMAIL'];
        foreach($arSubscriptionFields as $code){
            $data['SUBSCRIPTION_' . $code] = $arSubscription[$code];
        }

        $arRubric = \CRubric::GetByID($rubricId)->GetNext(true, false);

        $arRubricFields = ['NAME', 'DESCRIPTION'];
        foreach($arRubricFields as $code){
            $data['RUBRIC_' . $code] = $arRubric[$code];
        }

        return $data;
    }

    protected static function sendEvent(string $type, array $arEmailData): void
    {
        $event = new \CEvent;
        $event->Send($type, SITE_ID, $arEmailData);
    }
}