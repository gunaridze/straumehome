<?php
namespace Imedia\Main\Handlers;

use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\PhoneNumber\Format;
use Imedia\Main\Helpers\User\User as UserHelper;

class User
{
    public array $arFields = [];
    public array $arOldFields = [];
    public bool $isNew = false;

    private static ?self $instance = null;

    private array $errors = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function onBeforeUserAdd(&$arFields)
    {
        $handler = self::getInstance();
        $handler->arFields = $arFields;
        $handler->isNew = true;
        $handler->onAddnUpdate();

        if(!empty($handler->errors)){
            global $APPLICATION;
            $APPLICATION->throwException(implode(', ', $handler->errors));
            return false;
        }

        $arFields = $handler->arFields;
    }

    public static function onBeforeUserUpdate(&$arFields)
    {
        $handler = self::getInstance();
        $handler->arFields = $arFields;
        $handler->isNew = false;
        $handler->onAddnUpdate();

        if(!empty($handler->errors)){
            global $APPLICATION;
            $APPLICATION->throwException(implode(', ', $handler->errors));
            return false;
        }

        $arFields = $handler->arFields;
    }

    public static function onAfterUserAdd($arFields)
    {
        $unsetFields = [
            'PASSWORD',
            'CONFIRM_PASSWORD',
            'CHECKWORD'
        ];
        foreach($unsetFields as $code){
            if(isset($arFields[$code])){
                unset($arFields[$code]);
            }
        }

        $event = new \CEvent;
        $event->Send('USER_ADD', $arFields['SITE_ID'], $arFields);
    }

    protected function onAddnUpdate()
    {
        if(!$this->checkPhoneExists()){
            return false;
        }

        if(!$this->checkEmailExists()){
            return false;
        }
    }

    protected function checkPhoneExists(): bool
    {
        if(!$this->arFields['PERSONAL_PHONE']){
            return true;
        }

        $parsedPhone = Parser::getInstance()->parse($this->arFields['PERSONAL_PHONE']);

        if(!$parsedPhone->isValid()){
            $this->errors[] = Loc::getMessage('IMEDIA_MAIN_HANDLERS_USER_ERROR_PHONE_INCORRECT');
            return false;
        }

        $this->arFields['PERSONAL_PHONE'] = $parsedPhone->format(Format::E164);

        $userId = UserHelper::getIdByPhone($this->arFields['PERSONAL_PHONE']);
        if(
            ($userId > 0)
            && ((int) $this->arFields['ID'] !== $userId)

        ){
            $this->errors[] = Loc::getMessage('IMEDIA_MAIN_HANDLERS_USER_ERROR_PHONE_EXIST');
            return false;
        }

        return true;
    }

    protected function checkEmailExists(): bool
    {
        if(!$this->arFields['EMAIL']){
            return true;
        }

        if(!check_email($this->arFields['EMAIL'])){
            $this->errors[] = Loc::getMessage('IMEDIA_MAIN_HANDLERS_USER_ERROR_EMAIL_INCORRECT');
            return false;
        }

        $userId = UserHelper::getIdByEmail($this->arFields['EMAIL']);
        if(
            ($userId > 0)
            && ((int) $this->arFields['ID'] !== $userId)

        ){
            $this->errors[] = Loc::getMessage('IMEDIA_MAIN_HANDLERS_USER_ERROR_EMAIL_EXIST');
            return false;
        }

        return true;
    }
}