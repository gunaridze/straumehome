<?php
namespace Imedia\Component;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UserTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Personal extends \CBitrixComponent
{
    protected function getResult()
    {
        $userId = (int) CurrentUser::get()->getId();
        if(!($userId > 0)){
            return;
        }

        $arUser = UserTable::getList(
            [
                'select' => [
                    'NAME',
                    'LAST_NAME',
                    'EMAIL',
                    'PERSONAL_PHONE',
                    'PERSONAL_GENDER'
                ],
                'filter' => [
                    '=ID' => $userId
                ],
                'limit' => 1
            ]
        )->fetch();

        $this->arResult = [
            'fields' => $arUser
        ];

    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->getResult();
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }
}