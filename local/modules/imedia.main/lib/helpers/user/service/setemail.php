<?php
namespace Imedia\Main\Helpers\User\Service;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\UserTable;

class SetEmail
{
    public static function process(int $userId, string $password, string $repeatPassword, string $email): Result
    {
        $result = new Result();

        if(!check_email($email)){
            $result->addError(new Error(Loc::getMessage('IMEDIA_SET_EMAIL_ERROR_EMAIL_INCORRECT')));
            return $result;
        }

        $arUser = UserTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['=EMAIL' => $email],
                'limit' => 1
            ]
        )->fetch();
        if($arUser){
            $result->addError(new Error(Loc::getMessage('IMEDIA_SET_EMAIL_ERROR_EMAIL_USED')));
            return $result;
        }

        $user = new \CUser;
        $resultUserId = $user->Update($userId, [
            'EMAIL' => $email,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $repeatPassword
        ]);
        if(!($resultUserId > 0)){
            $result->addError(new Error($user->LAST_ERROR));
            return $result;
        }

        $result->setData(
            [
                'email' => $email
            ]
        );

        return $result;
    }
}