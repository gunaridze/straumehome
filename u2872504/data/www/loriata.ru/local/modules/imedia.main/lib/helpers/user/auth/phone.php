<?php
namespace Imedia\Main\Helpers\User\Auth;

use Bitrix\Main\UserPhoneAuthTable;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\PhoneNumber\Format;
use Imedia\Main\Helpers\User\User as UserHelper;

class Phone extends Base
{
    protected Result $result;
    protected string $phone;

    protected function __construct(string $phone)
    {
        $this->result = new Result();

        $parsedPhone = Parser::getInstance()->parse($phone);
        $this->phone = $parsedPhone->format(Format::E164);
    }

    protected static function createProcess(string $phone): self
    {
        $process = new static($phone);

        if(!UserPhoneAuthTable::validatePhoneNumber($process->phone)){
            $process->result->addError(new Error(Loc::getMessage('IMEDIA_AUTH_PHONE_ERROR_PHONE_INVALID')));
        }

        return $process;
    }

    public static function requestCode(string $phone): Result
    {
        $process = static::createProcess($phone);
        if(!$process->result->isSuccess()){
            return $process->result;
        }

        $userId = UserHelper::getIdByPhone($process->phone);

        if(!($userId > 0)){
            $createResult = UserHelper::createByPhone($process->phone);
            if(!($createResult->isSuccess())){
                $process->result->addErrors($createResult->getErrors());
                return $process->result;
            }
        }

        $sendResult = UserHelper::SendPhoneCode($process->phone, 'SMS_USER_CONFIRM_NUMBER', SITE_ID);
        if(!($sendResult->isSuccess())){
            $process->result->addErrors($sendResult->getErrors());
            return $process->result;
        }

        $process->result->setData(
            [
                'repeatTime' => (time() + UserHelper::PHONE_CODE_RESEND_INTERVAL) * 1000,
                'displayTime' => UserHelper::PHONE_CODE_RESEND_INTERVAL
            ]
        );

        return $process->result;

    }

    public static function submitCode(string $phone, string $code): Result
    {
        $process = static::createProcess($phone);
        if(!$process->result->isSuccess()){
            return $process->result;
        }

        $userId = UserHelper::VerifyPhoneCode($process->phone, $code);
        if(!($userId > 0)){
            $process->result->addError(new Error(Loc::getMessage('IMEDIA_AUTH_PHONE_ERROR_CODE_INVALID')));
            return $process->result;
        }

        $fUserId = static::getFUserId();
        $GLOBALS['USER']->Authorize($userId, true);
        static::transferData($fUserId, $userId);

        return $process->result;

    }
}