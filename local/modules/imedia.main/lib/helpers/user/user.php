<?php
namespace Imedia\Main\Helpers\User;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\UserTable;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Config\Option;
use Bitrix\Main\SiteTable;
use Bitrix\Main\UserPhoneAuthTable;
use Bitrix\Main\ArgumentException;
use Imedia\Main\Helpers\Security\Mfa\TotpAlgorithm;

class User extends \CUser
{
    public static function GeneratePhoneCode($userId)
    {
        $row = UserPhoneAuthTable::getRowById($userId);
        
        if($row && $row['OTP_SECRET'] <> ''){
            $totp = new TotpAlgorithm();
            $totp->setInterval(self::PHONE_CODE_OTP_INTERVAL);
            $totp->setSecret($row['OTP_SECRET']);

            $timecode = $totp->timecode(time());
            $code = $totp->generateOTP($timecode);

            UserPhoneAuthTable::update($userId, array(
                'ATTEMPTS' => 0,
                'DATE_SENT' => new \Bitrix\Main\Type\DateTime(),
            ));

            return [$code, $row['PHONE_NUMBER']];
        }
        
        return false;
    }

    public static function VerifyPhoneCode($phoneNumber, $code)
    {
        if($code == ''){
            return false;
        }

        $phoneNumber = UserPhoneAuthTable::normalizePhoneNumber($phoneNumber);

        $row = UserPhoneAuthTable::getList(
            [
                'filter' => ['=PHONE_NUMBER' => $phoneNumber],
                'limit' => 1
            ]
        )->fetch();
        if($row && $row['OTP_SECRET'] <> ''){
            if($row['ATTEMPTS'] >= 3){
                return false;
            }

            $totp = new TotpAlgorithm();
            $totp->setInterval(self::PHONE_CODE_OTP_INTERVAL);
            $totp->setSecret($row['OTP_SECRET']);

            try{
                list($result, ) = $totp->verify($code);
            } catch (ArgumentException $e){
                return false;
            }

            $data = [];
            if($result){
                if($row['CONFIRMED'] === 'N'){
                    $data['CONFIRMED'] = 'Y';
                }

                $data['DATE_SENT'] = '';
            } else {
                $data['ATTEMPTS'] = (int) $row['ATTEMPTS'] + 1;
            }

            if(!empty($data)){
                UserPhoneAuthTable::update($row['USER_ID'], $data);
            }

            if($result){
                return $row['USER_ID'];
            }
        }
        return false;
    }

    public static function createByPhone($phone, $arAdditionalFields = []): Result
    {
        $result = new Result();

        $user = new \CUser;
        $password = Random::getString(20, true);

        $arSite = SiteTable::getList(
            [
                'select' => ['LID'],
                'filter' => ['=DEF' => 'Y'],
                'limit' => 1
            ]
        )->fetch();

        $userId = (int) $user->Add(
            array_merge(
                [
                    'ACTIVE' => 'Y',
                    'LOGIN' => $phone,
                    'PHONE_NUMBER' => $phone,
                    'PERSONAL_PHONE' => $phone,
                    'LID' => $arSite['LID'],
                    'PASSWORD' => $password,
                    'PASSWORD_REPEAT' => $password,
                    'GROUP_ID' => explode(
                        ',',
                        Option::get('main', 'new_user_registration_def_group')
                    )
                ],
                $arAdditionalFields
            )
        );

        if(!($userId > 0)){
            $result->addError(new Error($user->LAST_ERROR));
            return $result;
        }

        $result->setData(['ID' => $userId]);

        return $result;
    }

    public static function getIdByPhone(string $phone): int
    {
        $arUser = UserTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['=PERSONAL_PHONE' => $phone],
                'limit' => 1
            ]
        )->fetch();

        return (int) $arUser['ID'];
    }

    public static function getIdByEmail(string $email): int
    {
        $arUser = UserTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['=EMAIL' => $email],
                'limit' => 1
            ]
        )->fetch();

        return (int) $arUser['ID'];
    }

    public static function getLoginByEmail(string $email): ?string
    {
        $arUser = UserTable::getList(
            [
                'select' => ['LOGIN'],
                'filter' => ['=EMAIL' => $email],
                'limit' => 1
            ]
        )->fetch();

        return $arUser['LOGIN'];
    }
}