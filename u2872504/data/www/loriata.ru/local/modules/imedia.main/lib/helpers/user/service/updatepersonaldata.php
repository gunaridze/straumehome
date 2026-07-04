<?php
namespace Imedia\Main\Helpers\User\Service;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;

class UpdatePersonalData
{
    public static function process(int $userId, string $name, string $lastName): Result
    {
        $result = new Result();

        if(
            !($userId > 0)
            || !$name
            || !$lastName
        ){
            $result->addError( new Error( Loc::getMessage('IMEDIA_USER_UPDATE_PERSONAL_DATA_INCORRECT_VALUE') ) );
            return $result;
        }

        $user = new \CUser;
        if(!$user->Update($userId, [
            'NAME' => $name,
            'LAST_NAME' => $lastName
        ])){
            $result->addError($user->LAST_ERROR);
        }

        $result->setData([
            'name' => $name,
            'lastName' => $lastName
        ]);

        return $result;
    }
}