<?php
namespace Imedia\Main\Helpers\User\Service;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\UserTable;

class ForgotPassword
{
    public static function process(int $userId): Result
    {
        $result = new Result();

        $arUser = UserTable::getList(
            [
                'select' => ['LOGIN', 'EMAIL'],
                'filter' => ['=ID' => $userId],
                'limit' => 1
            ]
        )->fetch();
        if(!$arUser['EMAIL']){
            $result->addError(new Error(Loc::getMessage('IMEDIA_FORGOT_PASSWORD_ERROR_EMAIL')));
            return $result;
        }

        $sendResult = \CUser::SendPassword($arUser['LOGIN'], $arUser['EMAIL']);
        if($sendResult['TYPE'] !== 'OK'){
            $result->addError(new Error($sendResult['MESSAGE']));
            return $result;
        }

        return $result;
    }
}