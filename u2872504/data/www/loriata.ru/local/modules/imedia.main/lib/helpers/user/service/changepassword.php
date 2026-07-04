<?php
namespace Imedia\Main\Helpers\User\Service;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\UserTable;

class ChangePassword
{
    public static function process(int $userId, string $password, string $newPassword, string $repeatPassword): Result
    {
        $result = new Result();

        $arUser = UserTable::getList(
            [
                'select' => ['LOGIN'],
                'filter' => ['=ID' => $userId],
                'limit' => 1
            ]
        )->fetch();
        if(!$arUser['LOGIN']){
            $result->addError(new Error(Loc::getMessage('IMEDIA_CHANGE_PASSWORD_ERROR_LOGIN')));
            return $result;
        }

        global $USER;
        $authResult = $USER->Login($arUser['LOGIN'], $password);
        if(is_array($authResult) || !$authResult){
            $result->addError(new Error(Loc::getMessage('IMEDIA_CHANGE_PASSWORD_ERROR_PASSWORD')));
            return $result;
        }

        $user = new \CUser;
        $resultUserId = $user->Update($userId, [
            'PASSWORD' => $newPassword,
            'CONFIRM_PASSWORD' => $repeatPassword
        ]);
        if(!($resultUserId > 0)){
            $result->addError(new Error($user->LAST_ERROR));
            return $result;
        }

        return $result;
    }
}