<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Result;
use Bitrix\Main\Engine\Response;
use Imedia\Main\Helpers\User\Auth as AuthHelper;

class Auth extends Controller
{
    public function configureActions()
    {
        return [
            'requestCode' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'submitCode' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'loginByEmail' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'registerByEmail' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'forgotPassword' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ]
        ];
    }

    public function requestCodeAction(string $phone)
    {
        try {

            $result = AuthHelper\Phone::requestCode($phone);
            if(!($result->isSuccess())){
                return Response\AjaxJson::createError( $result->getErrorCollection() );
            }

            return $result->getData();

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function submitCodeAction(string $phone, string $code)
    {
        try {

            $result = AuthHelper\Phone::submitCode($phone, $code);
            if(!($result->isSuccess())){
                return Response\AjaxJson::createError( $result->getErrorCollection() );
            }

            return $result->getData();

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function loginByEmailAction(string $email, string $password, bool $remember = false)
    {
        try {

            $result = AuthHelper\Email::login($email, $password, $remember);
            if(!($result->isSuccess())){
                return Response\AjaxJson::createError( $result->getErrorCollection() );
            }

            return $result->getData();

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function registerByEmailAction(
        string $name,
        string $lastName,
        string $email,
        string $password,
        string $passwordRepeat
    )
    {
        try {

            $result = AuthHelper\Email::register($name, $lastName, $email, $password, $passwordRepeat);
            if(!($result->isSuccess())){
                return Response\AjaxJson::createError( $result->getErrorCollection() );
            }

            return $result->getData();

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function forgotPasswordAction(string $email)
    {
        try {

            $result = AuthHelper\Email::forgotPassword($email);
            if(!($result->isSuccess())){
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