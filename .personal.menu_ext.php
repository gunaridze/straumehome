<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION;

$aMenuLinksExt = [
    [
        "Выйти",
        $APPLICATION->GetCurPageParam("logout=yes&".bitrix_sessid_get(), [
                "login",
                "logout",
                "register",
                "forgot_password",
                "change_password"]
        ),
        [],
        [],
        "\$GLOBALS['USER']->IsAuthorized()"
    ]
];

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);