<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Result;
use Bitrix\Main\Engine\Response;
use Imedia\Main\Helpers\User\Service;

class User extends Controller
{
	public function configureActions()
	{
		return [
            'updateGender' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ]
            ],
            'updatePersonalData' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ]
            ],
            'requestCode' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
            'submitCode' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
            'changeEmail' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
            'setEmail' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
            'changePassword' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
            'forgotPassword' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ]
		];
	}

    public function updateGenderAction(CurrentUser $currentUser, string $gender)
    {
        try {

            $gender = trim(filter_var(
                $gender,
                FILTER_SANITIZE_STRING
            ));

            $result = Service\UpdateGender::process($currentUser->getId(), $gender);
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

    public function updatePersonalDataAction(CurrentUser $currentUser, string $name, string $lastName)
    {
        try {

            $name = trim(filter_var(
                $name,
                FILTER_SANITIZE_STRING
            ));

            $lastName = trim(filter_var(
                $lastName,
                FILTER_SANITIZE_STRING
            ));

            $result = Service\UpdatePersonalData::process($currentUser->getId(), $name, $lastName);
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

    public function requestCodeAction(CurrentUser $currentUser, string $phone)
    {
        try {

            $phone = trim(filter_var(
                $phone,
                FILTER_SANITIZE_STRING
            ));

            $result = Service\ChangePhone::requestCode($currentUser->getId(), $phone);
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

    public function submitCodeAction(CurrentUser $currentUser, string $phone, string $code)
    {
        try {

            $phone = trim(filter_var(
                $phone,
                FILTER_SANITIZE_STRING
            ));

            $code = trim(filter_var(
                $code,
                FILTER_SANITIZE_NUMBER_INT
            ));

            $result = Service\ChangePhone::submitCode($currentUser->getId(), $phone, $code);
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

    public static function changeEmailAction(CurrentUser $currentUser, string $password, string $email)
    {
        try {

            $password = trim(filter_var(
                $password,
                FILTER_SANITIZE_STRING
            ));

            $email = trim(filter_var(
                $email,
                FILTER_SANITIZE_EMAIL
            ));

            $result = Service\ChangeEmail::process($currentUser->getId(), $password, $email);
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

    public static function setEmailAction(
        CurrentUser $currentUser,
        string $password,
        string $repeatPassword,
        string $email
    )
    {
        try {

            $password = trim(filter_var(
                $password,
                FILTER_SANITIZE_STRING
            ));

            $repeatPassword = trim(filter_var(
                $repeatPassword,
                FILTER_SANITIZE_STRING
            ));

            $email = trim(filter_var(
                $email,
                FILTER_SANITIZE_EMAIL
            ));

            $result = Service\SetEmail::process($currentUser->getId(), $password, $repeatPassword, $email);
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

    public static function changePasswordAction(
        CurrentUser $currentUser,
        string $password,
        string $newPassword,
        string $repeatPassword
    )
    {
        try {

            $password = trim(filter_var(
                $password,
                FILTER_SANITIZE_STRING
            ));

            $newPassword = trim(filter_var(
                $newPassword,
                FILTER_SANITIZE_STRING
            ));

            $repeatPassword = trim(filter_var(
                $repeatPassword,
                FILTER_SANITIZE_STRING
            ));

            $result = Service\ChangePassword::process($currentUser->getId(), $password, $newPassword, $repeatPassword);
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

    public static function forgotPasswordAction(CurrentUser $currentUser)
    {
        try{

            $result = Service\ForgotPassword::process($currentUser->getId());
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
