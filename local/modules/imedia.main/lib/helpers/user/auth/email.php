<?php
namespace Imedia\Main\Helpers\User\Auth;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\User\User as UserHelper;

class Email extends Base
{
    protected Result $result;

    public static function login(string $email, string $password, bool $remember = false): Result
    {
        $result = new Result();

        if(!check_email($email)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_AUTH_EMAIL_ERROR_EMAIL_INVALID')));
            return $result;
        }

        $login = UserHelper::getLoginByEmail($email);
        if(!$login){
            $result->addError(new Error(Loc::getMessage('IMEDIA_AUTH_EMAIL_ERROR_LOGIN_INVALID')));
            return $result;
        }

        $fUserId = static::getFUserId();

        global $USER;
        if (!is_object($USER)){
            $USER = new \CUser;
        }

        $remember = ($remember) ? 'Y' : 'N';

        $authResult = $USER->Login($login, $password, $remember);
        if(is_array($authResult)){
            $result->addError(new Error($authResult['MESSAGE'] ?: Loc::getMessage('IMEDIA_AUTH_EMAIL_ERROR_LOGIN_INVALID')));
            return $result;
        }

        $userId = UserHelper::getIdByEmail($email);

        static::transferData($fUserId, $userId);

        return $result;
    }

    public static function register(
        string $name,
        string $lastName,
        string $email,
        string $password,
        string $passwordRepeat
    ): Result
    {
        $result = new Result();

        if(!check_email($email)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_AUTH_EMAIL_ERROR_EMAIL_INVALID')));
            return $result;
        }

        $userId = UserHelper::getIdByEmail($email);
        if($userId > 0){
            $result->addError(new Error(Loc::getMessage('IMEDIA_AUTH_EMAIL_ERROR_EMAIL_USED')));
            return $result;
        }

        $fUserId = static::getFUserId();

        global $USER;
        if (!is_object($USER)){
            $USER = new \CUser;
        }

        $registerResult = $USER->Register(
            $email,
            $name,
            $lastName,
            $password,
            $passwordRepeat,
            $email
        );

        if($registerResult['TYPE'] !== 'OK'){
            $result->addError(new Error($registerResult['MESSAGE']));
            return $result;
        }

        $userId = UserHelper::getIdByEmail($email);

        static::transferData($fUserId, $userId);

        return $result;
    }

    public static function forgotPassword(string $email): Result
    {
        $result = new Result();

        if(!check_email($email)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_AUTH_EMAIL_ERROR_EMAIL_INVALID')));
            return $result;
        }

        $login = UserHelper::getLoginByEmail($email);
        if(!$login){
            $result->addError(new Error(Loc::getMessage('IMEDIA_AUTH_EMAIL_ERROR_EMAIL_NOT_FOUND')));
            return $result;
        }

        $sendResult = \CUser::SendPassword($login, $email);
        if($sendResult['TYPE'] !== 'OK'){
            $result->addError(new Error($sendResult['MESSAGE']));
            return $result;
        }

        return $result;
    }
}