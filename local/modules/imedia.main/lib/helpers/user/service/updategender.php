<?php
namespace Imedia\Main\Helpers\User\Service;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;

class UpdateGender
{
    public static function process(int $userId, string $gender): Result
    {
        $result = new Result();

        if(!($userId > 0)){
            $result->addError( new Error( Loc::getMessage('IMEDIA_USER_UPDATE_GENDER_INCORRECT_VALUE') ) );
            return $result;
        }

        $allowValues = ['M', 'F'];

        if(!in_array($gender, $allowValues, true)){
            $result->addError( new Error( Loc::getMessage('IMEDIA_USER_UPDATE_GENDER_INCORRECT_VALUE') ) );
            return $result;
        }

        $user = new \CUser;
        if(!$user->Update($userId, ['PERSONAL_GENDER' => $gender])){
            $result->addError($user->LAST_ERROR);
        }

        $result->setData(['gender' => $gender]);

        return $result;
    }
}