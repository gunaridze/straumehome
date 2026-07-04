<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UserTable;
use Bitrix\Main\Web\Json;

$currentUser = CurrentUser::get();

$arUser = [];
if($currentUser->getId() > 0){
    $arUser = UserTable::getList(
        [
            'select' => [
                'NAME',
                'LAST_NAME',
                'PERSONAL_PHONE',
                'EMAIL'
            ],
            'filter' => ['=ID' => $currentUser->getId()],
            'limit' => 1
        ]
    )->fetch();
}

$params = [
    'user' => $arUser,
    'signedParameters' => $component->getSignedParameters(),
    'componentName' => $component->getName(),
    'fields' => [
        [
            'code' => 'NAME',
            'placeholder' => Loc::getMessage('T_FORM_FEEDBACK_FIELD_PLACEHOLDER_NAME'),
            'type' => 'input',
            'inputType' => 'text',
            'value' => $arUser['NAME'],
            'defaultValue' => $arUser['NAME'],
            'required' => true
        ],
        [
            'code' => 'LAST_NAME',
            'placeholder' => Loc::getMessage('T_FORM_FEEDBACK_FIELD_PLACEHOLDER_LAST_NAME'),
            'type' => 'input',
            'inputType' => 'text',
            'value' => $arUser['LAST_NAME'],
            'defaultValue' => $arUser['LAST_NAME'],
            'required' => true
        ],
        [
            'code' => 'PHONE',
            'placeholder' => Loc::getMessage('T_FORM_FEEDBACK_FIELD_PLACEHOLDER_PHONE'),
            'type' => 'input',
            'inputType' => 'tel',
            'value' => $arUser['PERSONAL_PHONE'],
            'defaultValue' => $arUser['PERSONAL_PHONE'],
            'required' => true
        ],
        [
            'code' => 'EMAIL',
            'placeholder' => Loc::getMessage('T_FORM_FEEDBACK_FIELD_PLACEHOLDER_EMAIL'),
            'type' => 'input',
            'inputType' => 'email',
            'value' => $arUser['EMAIL'],
            'defaultValue' => $arUser['EMAIL'],
            'required' => true
        ],
        [
            'code' => 'MESS',
            'placeholder' => Loc::getMessage('T_FORM_FEEDBACK_FIELD_PLACEHOLDER_MESS'),
            'type' => 'textarea',
            'inputType' => null,
            'value' => null,
            'defaultValue' => null,
            'required' => false
        ]
    ]
];
?>
<div class="profile-page__subtitle"><?=Loc::getMessage('T_FORM_FEEDBACK_TITLE')?></div>
<form-feedback :params='<?=Json::encode($params)?>'></form-feedback>