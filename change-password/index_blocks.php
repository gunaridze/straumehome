<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)	die();

global $APPLICATION;

$APPLICATION->IncludeComponent(
    'imedia:main.auth.changepasswd',
    '',
    [],
    false,
    ['HIDE_ICONS' => true]
);
