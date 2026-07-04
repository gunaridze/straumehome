<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)	die();

global $APPLICATION;

$APPLICATION->IncludeComponent(
    'imedia:form',
    'feedback',
    [
        'FORM_CODE' => 'FEEDBACK'
    ],
    false,
    ['HIDE_ICONS' => true]
);
