<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Response;
use Imedia\Main\Helpers\Subscription as SubscriptionHelper;
use Imedia\Main\Helpers\Iblock\Info;

class Subscription extends Controller
{
    public function configureActions()
    {
        return [
            'subscribeFooter' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf()
                ]
            ],
            'getItems' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET]
                    ),
                    new ActionFilter\Csrf(),
                    new ActionFilter\Authentication(),
                ]
            ],
            'subscribe' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                    new ActionFilter\Authentication(),
                ]
            ],
            'unsubscribe' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                    new ActionFilter\Authentication(),
                ]
            ],
            'couponForSubscribe' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf()
                ]
            ],
            'couponForSubscribeCheck' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET]
                    ),
                    new ActionFilter\Csrf()
                ]
            ]
        ];
    }

    public function subscribeFooterAction(string $email, CurrentUser $currentUser)
    {
        try {

            $email = trim(filter_var(
                $email,
                FILTER_SANITIZE_EMAIL
            ));

            $result = SubscriptionHelper::subscribeFooter($email, $currentUser);

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

    public function getItemsAction(CurrentUser $currentUser)
    {
        try{

            $result = SubscriptionHelper::getList($currentUser);

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

    public function subscribeAction(int $rubricId, CurrentUser $currentUser)
    {
        try{

            $result = SubscriptionHelper::subscribe($rubricId, $currentUser);

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

    public function unsubscribeAction(int $rubricId, int $subscriptionId, CurrentUser $currentUser)
    {
        try{

            $result = SubscriptionHelper::unsubscribe($rubricId, $subscriptionId, $currentUser);

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

    public function couponForSubscribeAction(string $email, CurrentUser $currentUser)
    {
        try {

            $email = trim(filter_var(
                $email,
                FILTER_SANITIZE_EMAIL
            ));

            $result = SubscriptionHelper::couponForSubscribe($email, $currentUser);
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

    public function couponForSubscribeCheckAction(CurrentUser $currentUser)
    {
        try{

            $session = Application::getInstance()->getSession();
            $session->set(Info::SESSION_CODE_COUPON, 1);

            $result = SubscriptionHelper::couponForSubscribeCheck($currentUser);
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