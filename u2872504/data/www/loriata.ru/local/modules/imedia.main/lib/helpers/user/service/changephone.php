<?php
namespace Imedia\Main\Helpers\User\Service;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\UserPhoneAuthTable;
use Bitrix\Main\Sms;
use Imedia\Main\Helpers\User\User as UserHelper;
use Imedia\Main\Models\VerifyByPhone\VerifyByPhoneTable;
use Imedia\Main\Models\VerifyByPhone\VerifyByPhone;
use Imedia\Main\Helpers\Security\Mfa\TotpAlgorithm;

class ChangePhone
{
    protected Result $result;
    protected int $userId;
    protected string $phone;

    const ATTEMPTS_LIMIT = 3;

    protected function __construct(int $userId, string $phone)
    {
        $this->result = new Result();

        $this->userId = $userId;

        $parsedPhone = Parser::getInstance()->parse($phone);
        $this->phone = $parsedPhone->format(Format::E164);
    }

    protected static function createProcess(int $userId, string $phone): self
    {
        $process = new static($userId, $phone);

        if(!UserPhoneAuthTable::validatePhoneNumber($process->phone)){
            $process->result->addError(new Error(Loc::getMessage('IMEDIA_CHANGE_PHONE_ERROR_PHONE_INVALID')));
        }

        return $process;
    }

    public static function requestCode(int $userId, string $phone): Result
    {
        $process = static::createProcess($userId, $phone);
        if(!$process->result->isSuccess()){
            return $process->result;
        }

        $userIdByPhone = UserHelper::getIdByPhone($process->phone);
        if($userIdByPhone > 0){
            $process->result->addError(new Error(Loc::getMessage('IMEDIA_CHANGE_PHONE_ERROR_PHONE_USED')));
            return $process->result;
        }

        $process->getVerifyData();

        $result = VerifyByPhoneTable::Update(
            $userId,
            [
                'PHONE_NUMBER' => $process->phone,
                'OTP_SECRET' => time(),
                'USED' => false,
                'DATE' => new \Bitrix\Main\Type\DateTime(),
                'ATTEMPTS' => 0
            ]
        );
        if(!($result->isSuccess())){
            $process->result->addErrors($result->getErrors());
            return $process->result;
        }

        $verifyData = $process->getVerifyData();

        $otp = new TotpAlgorithm();
        $otp->setSecret($verifyData->getOtpSecret());
        $otp->setInterval(UserHelper::PHONE_CODE_OTP_INTERVAL);
        $timecode = $otp->timecode(time());
        $code = $otp->generateOTP($timecode);

        $sms = new Sms\Event(
            'SMS_USER_CONFIRM_NUMBER',
            [
                "USER_PHONE" => $process->phone,
                "CODE" => $code,
            ]
        );

        $sms->setSite(SITE_ID);
        $sms->setLanguage(LANGUAGE_ID);

        $result = $sms->send(true);
        if(!($result->isSuccess())){
            $process->result->addErrors($result->getErrors());
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

    protected function getVerifyData(): VerifyByPhone
    {
        $obj = VerifyByPhoneTable::getByPrimary(['USER_ID' => $this->userId])->fetchObject();
        if(!$obj){

            VerifyByPhoneTable::Add(
                [
                    'PHONE_NUMBER' => $this->phone,
                    'USER_ID' => $this->userId,
                    'OTP_SECRET' => time(),
                    'USED' => false,
                    'DATE' => new \Bitrix\Main\Type\DateTime()
                ]
            );

        }

        $obj = VerifyByPhoneTable::getByPrimary(['USER_ID' => $this->userId])->fetchObject();
        return $obj;
    }

    public static function submitCode(int $userId, string $phone, string $code): Result
    {
        $process = static::createProcess($userId, $phone);
        if(!$process->result->isSuccess()){
            return $process->result;
        }

        $verifyData = $process->getVerifyData();
        if((int) $verifyData->getAttempts() >= static::ATTEMPTS_LIMIT){
            $process->result->addError(new Error(Loc::getMessage('IMEDIA_CHANGE_PHONE_ERROR_ATTEMPTS_LIMIT')));
            return $process->result;
        }

        if($verifyData->getPhoneNumber() !== $process->phone){
            $process->result->addError(new Error(Loc::getMessage('IMEDIA_CHANGE_PHONE_ERROR_PHONE_CHANGE')));
            return $process->result;
        }

        if($verifyData->getUsed()){
            $process->result->addError(new Error(Loc::getMessage('IMEDIA_CHANGE_PHONE_ERROR_USED')));
            return $process->result;
        }

        $otp = new TotpAlgorithm();
        $otp->setSecret($verifyData->getOtpSecret());
        $otp->setInterval(UserHelper::PHONE_CODE_OTP_INTERVAL);

        list($isSuccess, ) = $otp->verify($code);

        $verifyData->setAttempts((int) $verifyData->getAttempts() + 1);
        if($isSuccess){
            $verifyData->setUsed(true);
        }
        $verifyData->save();

        if(!$isSuccess){
            $process->result->addError(new Error(Loc::getMessage('IMEDIA_CHANGE_PHONE_ERROR_VERIFY_FAIL')));
            return $process->result;
        }

        $user = new \CUser;
        $resultUserId = $user->Update($userId, [
            'PHONE_NUMBER' => $process->phone,
            'PERSONAL_PHONE' => $process->phone
        ]);
        if(!($resultUserId > 0)){
            $process->result->addError(new Error($user->LAST_ERROR));
            return $process->result;
        }

        $process->result->setData(
            [
                'phone' => $process->phone
            ]
        );

        return $process->result;
    }
}