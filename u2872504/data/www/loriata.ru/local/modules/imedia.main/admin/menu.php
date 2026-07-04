<?php

use Bitrix\Main\Localization\Loc;

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler("main", "OnBuildGlobalMenu",
    "onBuildGlobalMenuHandlerImediaMain");

function onBuildGlobalMenuHandlerImediaMain(&$arGlobalMenu, &$arModuleMenu)
{
    IncludeModuleLangFile(__FILE__);
    $moduleID = "imedia.main";

    $path = str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__);
    $GLOBALS['APPLICATION']->SetAdditionalCss($path . "/css/menu.css");

    if ($GLOBALS['APPLICATION']->GetGroupRight($moduleID) >= 'R') {

        $arMenu = [
            "menu_id" => "global-menu-imedia-main",
            "text" => Loc::getMessage("IMEDIA_MAIN_GLOBAL_MENU_TEXT"),
            "title" => Loc::getMessage("IMEDIA_MAIN_GLOBAL_MENU_TITLE"),
            "sort" => 1000,
            "items_id" => "global-menu-media-main-items",
            "items" => [
                [
                    "text" => Loc::getMessage("IMEDIA_MAIN_GLOBAL_MENU_SITE_SETTINGS_TEXT"),
                    "title" => Loc::getMessage("IMEDIA_MAIN_GLOBAL_MENU_SITE_SETTINGS_TEXT"),
                    "sort" => 200,
                    "url" => "/bitrix/admin/" . $moduleID . "_site_settings.php",
                    "icon" => "sys_menu_icon",
                    "page_icon" => "pi_control_center",
                    "items_id" => "site_settings",
                ],
                [
                    "text" => Loc::getMessage("IMEDIA_MAIN_GLOBAL_MENU_CATALOG_UPDATE_TEXT"),
                    "title" => Loc::getMessage("IMEDIA_MAIN_GLOBAL_MENU_CATALOG_UPDATE_TEXT"),
                    "sort" => 300,
                    "url" => "/bitrix/admin/" . $moduleID . "_catalog_update.php",
                    "icon" => "iblock_menu_icon_settings",
                    "page_icon" => "pi_control_center",
                    "items_id" => "catalog_update",
                ],
            ],
        ];

        $arGlobalMenu[] = $arMenu;

    }
}