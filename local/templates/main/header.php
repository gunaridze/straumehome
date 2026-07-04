<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use Imedia\Main\App;
use Imedia\Main\Helpers\Sale\Cart;
use Imedia\Main\Helpers\Iblock\Info;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Catalog\Section as SectionHelper;

$app = App::getInstance();
$elementIdInformation = Info::getId();

$app->setAsset();

$cartItems = Cart::refresh();
$cartItemsCount = count($cartItems);

$pageTemplate = $APPLICATION->GetProperty('page-template');
$currentUser = CurrentUser::get();

$app->jsCoreInit(['fx', 'ajax', 'currency', 'ls', 'date']);
?>
    <!DOCTYPE html>
<html xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>" class="no-js">
    <head>
        <?php $app->showHead()?>
        <title><?php $APPLICATION->ShowTitle();?></title>
        <?php $APPLICATION->IncludeFile("/local/include/".SITE_ID."/head.php", [], ["MODE" => "html"]);?>
        <script data-skip-moving="true">
            document.documentElement.className = document.documentElement.className.replace(/\bno-js\b/, 'js');
            if (!('ontouchstart' in document.documentElement)) {
                document.documentElement.className += ' no-touchevents';
            } else {
                document.documentElement.className += ' touchevents';
            }
        </script>
    </head>
<body>
<?php $APPLICATION->IncludeFile("/local/include/".SITE_ID."/body-begin.php", [], ["MODE" => "html"]);?>
<?php $APPLICATION->IncludeComponent('imedia:html.include', 'header-no-js', [], false, ['HIDE_ICONS' => true])?>
    <div id="panel"><?php $APPLICATION->ShowPanel();?></div>
<div class="wrapper" id="app">
    <header class="header">
        <div class="header__top">
            <div class="container header__top-inner">
                <?php $APPLICATION->IncludeComponent('imedia:html.include', 'header-shops', [], false, ['HIDE_ICONS' => true])?>
                <?php if($elementIdInformation > 0): ?>
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:news.detail",
                        "header-contact",
                        Array(
                            "ACTIVE_DATE_FORMAT" => "d.m.Y",
                            "ADD_ELEMENT_CHAIN" => "N",
                            "ADD_SECTIONS_CHAIN" => "N",
                            "AJAX_MODE" => "N",
                            "AJAX_OPTION_ADDITIONAL" => "",
                            "AJAX_OPTION_HISTORY" => "N",
                            "AJAX_OPTION_JUMP" => "N",
                            "AJAX_OPTION_STYLE" => "Y",
                            "BROWSER_TITLE" => "-",
                            "CACHE_GROUPS" => "N",
                            "CACHE_TIME" => "36000000",
                            "CACHE_TYPE" => "A",
                            "CHECK_DATES" => "Y",
                            "DETAIL_URL" => "",
                            "DISPLAY_BOTTOM_PAGER" => "N",
                            "DISPLAY_DATE" => "N",
                            "DISPLAY_NAME" => "N",
                            "DISPLAY_PICTURE" => "N",
                            "DISPLAY_PREVIEW_TEXT" => "N",
                            "DISPLAY_TOP_PAGER" => "N",
                            "ELEMENT_CODE" => "",
                            "ELEMENT_ID" => $elementIdInformation,
                            "FIELD_CODE" => array(""),
                            "IBLOCK_ID" => IblockHelper::getId('INFO'),
                            "IBLOCK_TYPE" => 'content',
                            "IBLOCK_URL" => "",
                            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                            "MESSAGE_404" => "",
                            "META_DESCRIPTION" => "-",
                            "META_KEYWORDS" => "-",
                            "PAGER_BASE_LINK_ENABLE" => "N",
                            "PAGER_SHOW_ALL" => "N",
                            "PAGER_TEMPLATE" => ".default",
                            "PAGER_TITLE" => "Страница",
                            "PROPERTY_CODE" => array('PHONE', 'WHATSAPP'),
                            "SET_BROWSER_TITLE" => "N",
                            "SET_CANONICAL_URL" => "N",
                            "SET_LAST_MODIFIED" => "N",
                            "SET_META_DESCRIPTION" => "N",
                            "SET_META_KEYWORDS" => "N",
                            "SET_STATUS_404" => "N",
                            "SET_TITLE" => "N",
                            "SHOW_404" => "N",
                            "STRICT_SECTION_CHECK" => "N",
                            "USE_PERMISSIONS" => "N",
                            "USE_SHARE" => "N"
                        )
                    );?>
                <?php endif ?>
            </div>
        </div>
        <?php $APPLICATION->IncludeComponent(
            "bitrix:news.detail",
            "header-notification",
            Array(
                "ACTIVE_DATE_FORMAT" => "d.m.Y",
                "ADD_ELEMENT_CHAIN" => "N",
                "ADD_SECTIONS_CHAIN" => "N",
                "AJAX_MODE" => "N",
                "AJAX_OPTION_ADDITIONAL" => "",
                "AJAX_OPTION_HISTORY" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "BROWSER_TITLE" => "-",
                "CACHE_GROUPS" => "N",
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "A",
                "CHECK_DATES" => "Y",
                "DETAIL_URL" => "",
                "DISPLAY_BOTTOM_PAGER" => "N",
                "DISPLAY_DATE" => "N",
                "DISPLAY_NAME" => "N",
                "DISPLAY_PICTURE" => "N",
                "DISPLAY_PREVIEW_TEXT" => "N",
                "DISPLAY_TOP_PAGER" => "N",
                "ELEMENT_CODE" => "",
                "ELEMENT_ID" => $elementIdInformation,
                "FIELD_CODE" => array(""),
                "IBLOCK_ID" => IblockHelper::getId('INFO'),
                "IBLOCK_TYPE" => 'content',
                "IBLOCK_URL" => "",
                "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                "MESSAGE_404" => "",
                "META_DESCRIPTION" => "-",
                "META_KEYWORDS" => "-",
                "PAGER_BASE_LINK_ENABLE" => "N",
                "PAGER_SHOW_ALL" => "N",
                "PAGER_TEMPLATE" => ".default",
                "PAGER_TITLE" => "Страница",
                "PROPERTY_CODE" => ['NOTIFICATION', 'NOTIFICATION_REPEAT', 'NOTIFICATION_SPEED'],
                "SET_BROWSER_TITLE" => "N",
                "SET_CANONICAL_URL" => "N",
                "SET_LAST_MODIFIED" => "N",
                "SET_META_DESCRIPTION" => "N",
                "SET_META_KEYWORDS" => "N",
                "SET_STATUS_404" => "N",
                "SET_TITLE" => "N",
                "SHOW_404" => "N",
                "STRICT_SECTION_CHECK" => "N",
                "USE_PERMISSIONS" => "N",
                "USE_SHARE" => "N"
            )
        );?>
        <div class="header__main">
            <div class="container">
                <div class="header__main-top">
                    <?php $APPLICATION->IncludeComponent(
                        'imedia:catalog.selected',
                        'header',
                        [],
                        false,
                        ['HIDE_ICONS' => true]
                    ) ?>
                    <button class="burger-btn" type="button" aria-label="<?=Loc::getMessage('T_HEADER_MENU_OPEN')?>">
                        <img
                                src="<?=SITE_TEMPLATE_PATH?>/assets/images/icons/burger.svg"
                                alt="<?=Loc::getMessage('T_HEADER_MENU_OPEN')?>"
                        >
                    </button>
                    <div class="logo header__logo">
                        <?php $logo = '<img class="logo__img header__logo-img" src="'.SITE_TEMPLATE_PATH.'/assets/images/logo/logo.svg" alt="'.$app->getSiteParam('SITE_NAME').'" width="207" height="31">';?>
                        <?php if($app->isFront()):?>
                            <div class="logo__link"><?=$logo?></div>
                        <?php else :?>
                            <a class="logo__link" href="<?=SITE_DIR?>" title="<?=$app->getSiteParam('SITE_NAME')?>"><?=$logo?></a>
                        <?php endif?>
                    </div>
                    <div class="header__actions">
                        <button class="header__search-btn" type="button"></button>
                        <?php if($currentUser->getId() > 0):?>
                            <a href="<?=SITE_DIR?>personal/" class="header__action" title="<?=Loc::getMessage('T_HEADER_PERSONAL')?>">
                                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.0013 8.50016C5.88797 8.50016 4.16797 6.78016 4.16797 4.66683C4.16797 2.5535 5.88797 0.833496 8.0013 0.833496C10.1146 0.833496 11.8346 2.5535 11.8346 4.66683C11.8346 6.78016 10.1146 8.50016 8.0013 8.50016Z" fill="#101112" />
                                    <path d="M13.7268 15.1667H8.0001H2.27344C2.0001 15.1667 1.77344 14.94 1.77344 14.6667C1.77344 11.82 4.56677 9.5 8.0001 9.5C11.4334 9.5 14.2268 11.82 14.2268 14.6667C14.2268 14.94 14.0001 15.1667 13.7268 15.1667Z" fill="#101112" />
                                </svg>
                                <span class="header__action-text"><?=($currentUser->getFirstName()) ?: Loc::getMessage('T_HEADER_PERSONAL')?></span>
                            </a>
                        <?php else: ?>
                            <auth-button class="header__action">
                                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.0013 8.50016C5.88797 8.50016 4.16797 6.78016 4.16797 4.66683C4.16797 2.5535 5.88797 0.833496 8.0013 0.833496C10.1146 0.833496 11.8346 2.5535 11.8346 4.66683C11.8346 6.78016 10.1146 8.50016 8.0013 8.50016Z" fill="#101112" />
                                    <path d="M13.7268 15.1667H8.0001H2.27344C2.0001 15.1667 1.77344 14.94 1.77344 14.6667C1.77344 11.82 4.56677 9.5 8.0001 9.5C11.4334 9.5 14.2268 11.82 14.2268 14.6667C14.2268 14.94 14.0001 15.1667 13.7268 15.1667Z" fill="#101112" />
                                </svg>
                                <span class="header__action-text"><?=Loc::getMessage('T_HEADER_PERSONAL')?></span>
                            </auth-button>
                        <?php endif?>
                        <a href="<?=SITE_DIR?>favorites/" class="header__action" title="<?=Loc::getMessage('T_HEADER_FAVORITES')?>">
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.9987 14.4331C7.79203 14.4331 7.59203 14.4064 7.42536 14.3464C4.8787 13.4731 0.832031 10.3731 0.832031 5.79307C0.832031 3.45974 2.7187 1.56641 5.0387 1.56641C6.16536 1.56641 7.2187 2.00641 7.9987 2.79307C8.7787 2.00641 9.83203 1.56641 10.9587 1.56641C13.2787 1.56641 15.1654 3.46641 15.1654 5.79307C15.1654 10.3797 11.1187 13.4731 8.57203 14.3464C8.40536 14.4064 8.20536 14.4331 7.9987 14.4331Z" fill="#101112" />
                            </svg>
                            <span class="header__action-text"><?=Loc::getMessage('T_HEADER_FAVORITES')?></span>
                            <favorites-counter></favorites-counter>
                        </a>
                        <cart-button class="header__action">
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.9933 15.1668H4.99996C3.85329 15.1668 2.99329 14.8602 2.45996 14.2535C1.92662 13.6468 1.71995 12.7668 1.85995 11.6268L2.45996 6.62683C2.63329 5.1535 3.00663 3.8335 5.60662 3.8335H10.4066C13 3.8335 13.3733 5.1535 13.5533 6.62683L14.1533 11.6268C14.2866 12.7668 14.0866 13.6535 13.5533 14.2535C13 14.8602 12.1466 15.1668 10.9933 15.1668Z" fill="#101112" />
                                <path d="M10.6673 5.8335C10.394 5.8335 10.1673 5.60683 10.1673 5.3335V3.00016C10.1673 2.28016 9.72065 1.8335 9.00065 1.8335H7.00065C6.28065 1.8335 5.83398 2.28016 5.83398 3.00016V5.3335C5.83398 5.60683 5.60732 5.8335 5.33398 5.8335C5.06065 5.8335 4.83398 5.60683 4.83398 5.3335V3.00016C4.83398 1.72683 5.72732 0.833496 7.00065 0.833496H9.00065C10.274 0.833496 11.1673 1.72683 11.1673 3.00016V5.3335C11.1673 5.60683 10.9407 5.8335 10.6673 5.8335Z" fill="#101112" />
                            </svg>
                            <span class="header__action-text"><?=Loc::getMessage('T_HEADER_CART')?></span>
                            <?php if($cartItemsCount > 0):?>
                                <span class="header__action-num">(<?=$cartItemsCount?>)</span>
                            <?php endif?>
                        </cart-button>
                    </div>
                </div>
                <div class="header__main-bottom">
                    <?php $APPLICATION->IncludeComponent(
                        'imedia:catalog.menu',
                        '.default',
                        [
                            'LINK_CATALOG' => '/catalog/',
                            'LINK_BRANDS' => '/brands/',
                            'LINK_BLOG' => '/blog/',
                            'LINK_NEWS' => '/news/',
                            'LINK_SALE' => SectionHelper::CODE_SALE.'/',
                            'LINK_NEW' => SectionHelper::CODE_NEW.'/'
                        ],
                        false,
                        ['HIDE_ICONS' => true]
                    ) ?>
                    <?php
                    $APPLICATION->IncludeComponent(
                        'imedia:html.include',
                        'header-search',
                        [],
                        false,
                        ['HIDE_ICONS' => true]
                    );
                    ?>
                </div>
            </div>
        </div>
    </header>
    <div class="burger-menu">
        <div class="burger-menu__top">
            <button
                    class="burger-menu__close"
                    type="button"
                    aria-label="<?=Loc::getMessage('T_HEADER_MENU_CLOSE')?>"
            >
                <img
                        src="<?=SITE_TEMPLATE_PATH?>/assets/images/icons/close-thin.svg"
                        alt="<?=Loc::getMessage('T_HEADER_MENU_CLOSE')?>"
                >
            </button>
            <?php $APPLICATION->IncludeComponent(
                'imedia:catalog.selected',
                'header',
                [],
                false,
                ['HIDE_ICONS' => true]
            ) ?>
        </div>
        <?php $APPLICATION->IncludeComponent(
            'imedia:catalog.menu',
            'mobile',
            [
                'LINK_CATALOG' => '/catalog/',
                'LINK_BRANDS' => '/brands/',
                'LINK_BLOG' => '/blog/',
                'LINK_NEWS' => '/news/',
                'LINK_SALE' => '/catalog/sale/',
                'LINK_NEW' => '/catalog/new/'
            ],
            false,
            ['HIDE_ICONS' => true]
        ) ?>
        <div class="burger-menu__bottom">
            <?php $APPLICATION->IncludeComponent('imedia:html.include', 'header-shops', [], false, ['HIDE_ICONS' => true])?>
            <?php if($elementIdInformation > 0): ?>
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:news.detail",
                    "header-contact",
                    Array(
                        "ACTIVE_DATE_FORMAT" => "d.m.Y",
                        "ADD_ELEMENT_CHAIN" => "N",
                        "ADD_SECTIONS_CHAIN" => "N",
                        "AJAX_MODE" => "N",
                        "AJAX_OPTION_ADDITIONAL" => "",
                        "AJAX_OPTION_HISTORY" => "N",
                        "AJAX_OPTION_JUMP" => "N",
                        "AJAX_OPTION_STYLE" => "Y",
                        "BROWSER_TITLE" => "-",
                        "CACHE_GROUPS" => "N",
                        "CACHE_TIME" => "36000000",
                        "CACHE_TYPE" => "A",
                        "CHECK_DATES" => "Y",
                        "DETAIL_URL" => "",
                        "DISPLAY_BOTTOM_PAGER" => "N",
                        "DISPLAY_DATE" => "N",
                        "DISPLAY_NAME" => "N",
                        "DISPLAY_PICTURE" => "N",
                        "DISPLAY_PREVIEW_TEXT" => "N",
                        "DISPLAY_TOP_PAGER" => "N",
                        "ELEMENT_CODE" => "",
                        "ELEMENT_ID" => $elementIdInformation,
                        "FIELD_CODE" => array(""),
                        "IBLOCK_ID" => IblockHelper::getId('INFO'),
                        "IBLOCK_TYPE" => 'content',
                        "IBLOCK_URL" => "",
                        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                        "MESSAGE_404" => "",
                        "META_DESCRIPTION" => "-",
                        "META_KEYWORDS" => "-",
                        "PAGER_BASE_LINK_ENABLE" => "N",
                        "PAGER_SHOW_ALL" => "N",
                        "PAGER_TEMPLATE" => ".default",
                        "PAGER_TITLE" => "Страница",
                        "PROPERTY_CODE" => array('PHONE', 'WHATSAPP'),
                        "SET_BROWSER_TITLE" => "N",
                        "SET_CANONICAL_URL" => "N",
                        "SET_LAST_MODIFIED" => "N",
                        "SET_META_DESCRIPTION" => "N",
                        "SET_META_KEYWORDS" => "N",
                        "SET_STATUS_404" => "N",
                        "SET_TITLE" => "N",
                        "SHOW_404" => "N",
                        "STRICT_SECTION_CHECK" => "N",
                        "USE_PERMISSIONS" => "N",
                        "USE_SHARE" => "N"
                    )
                );?>
            <?php endif ?>
        </div>
    </div>
<main class="page <?=$APPLICATION->ShowProperty('classes--page')?>">
<?php if(
    !$app->isFront()
    && !$app->isIndexCatalog()
    && !$app->is404()
):?>
    <?php $APPLICATION->IncludeComponent(
        "bitrix:breadcrumb",
        "main",
        [
            "PATH" => "",
            "START_FROM" => "0",
            "COMPONENT_TEMPLATE" => "main"
        ],
        false
    );?>
    <?php $APPLICATION->ShowViewContent('banner-catalog-top'); ?>
<?php endif;?>
<?php if(!$app->isIndexCatalog()):?>
<div class="container <?=$APPLICATION->ShowProperty('classes--container')?>">
    <?php if(!$app->isFront()): ?>
    <?php $APPLICATION->ShowViewContent('section-description-top');?>
    <?php $APPLICATION->ShowViewContent('title'); ?>
    <?php $APPLICATION->ShowViewContent('subtitle'); ?>
    <?php if($pageTemplate === 'menu-left'):
    $asideMenuType = $APPLICATION->GetProperty('aside-menu-type') ?: 'left';
    ?>
    <div class="profile-page__inner">
    <nav class="page-nav">
        <?php $APPLICATION->IncludeComponent(
            "bitrix:menu",
            "left",
            Array(
                "ALLOW_MULTI_SELECT" => "N",
                "CHILD_MENU_TYPE" => $asideMenuType,
                "DELAY" => "N",
                "MAX_LEVEL" => "1",
                "MENU_CACHE_GET_VARS" => [],
                "MENU_CACHE_TIME" => "360000",
                "MENU_CACHE_TYPE" => "N",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "ROOT_MENU_TYPE" => $asideMenuType,
                "USE_EXT" => "Y"
            )
        );?>
    </nav>
    <div class="profile-page__body">
    <?php $APPLICATION->ShowViewContent('title-additional'); ?>
<?php endif?>
<?php endif ?>
<?php endif?>