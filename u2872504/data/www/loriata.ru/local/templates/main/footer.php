<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use Imedia\Main\App;
use Imedia\Main\Helpers\Iblock\Info;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Catalog\Section as SectionHelper;
use Imedia\Main\Helpers\Catalog\Selected;
use Imedia\Main\Helpers\Common;

$app = App::getInstance();
$elementIdInformation = Info::getId();
$selectedCatalog = Selected::get();
$pageTemplate = $APPLICATION->GetProperty('page-template');
$currentUser = CurrentUser::get();
?>
<?php if($app->isIndexCatalog()): ?>
    <?php $app->includeContent('index_catalog', true);?>
<?php endif ?>
<?php $app->includeContent();?>
<?php if(!($app->isFront())): ?>
    <?php if($pageTemplate === 'menu-left'):?>
        </div><?php // profile-page__body ?>
        </div><?php // profile-page__inner ?>
    <?php endif ?>
<?php endif ?>
<?php if(!$app->isIndexCatalog()):?>
    </div><?php // container ?>
<?php endif ?>
</main>
<footer class="footer">
    <div class="footer__top">
        <div class="container footer__inner footer__inner--top">
            <div class="logo footer__logo">
                <?php $logo = '<img class="logo__img footer__logo-img" src="'.SITE_TEMPLATE_PATH.'/assets/images/logo/logo.svg" alt="'.$app->getSiteParam('SITE_NAME').'" width="127" height="25" loading="lazy">'; ?>
                <?php if($app->isFront()):?>
                    <div class="logo__link"><?=$logo?></div>
                <?php else :?>
                    <a class="logo__link" href="<?=SITE_DIR?>" title="<?=$app->getSiteParam('SITE_NAME')?>"><?=$logo?></a>
                <?php endif?>
            </div>
            <?php if($elementIdInformation > 0): ?>
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:news.detail",
                    "footer-social",
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
                        "PROPERTY_CODE" => array('SOCIAL'),
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
            <div class="footer__menus">
                <?php $APPLICATION->IncludeComponent(
                    'imedia:catalog.selected',
                    'footer',
                    [],
                    false,
                    ['HIDE_ICONS' => true]
                ) ?>
                <?php $APPLICATION->IncludeComponent(
                    'imedia:catalog.menu',
                    'footer',
                    [
                        'SHORT' => 'Y',
                        'LINK_CATALOG' => '/catalog/',
                        'LINK_SALE' => SectionHelper::CODE_SALE.'/',
                        'LINK_NEW' => SectionHelper::CODE_NEW.'/'
                    ],
                    false,
                    ['HIDE_ICONS' => true]
                ) ?>
            </div>
        </div>
    </div>
    <div class="footer__main">
        <div class="container">
            <div class="footer__inner footer__inner--main">
                <?php if($elementIdInformation > 0): ?>
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:news.detail",
                        "footer-contact",
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
                            "PROPERTY_CODE" => array('PHONE', 'HOURS'),
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
                <?php $APPLICATION->IncludeComponent('imedia:html.include', 'footer-subscription', [], false, ['HIDE_ICONS' => true])?>
                <?php
                $APPLICATION->IncludeComponent(
                    "bitrix:news.list",
                    "menu-footer",
                    array(
                        "ACTIVE_DATE_FORMAT" => "d.m.y",
                        "ADD_SECTIONS_CHAIN" => "N",
                        "AJAX_MODE" => "N",
                        "AJAX_OPTION_ADDITIONAL" => "",
                        "AJAX_OPTION_HISTORY" => "N",
                        "AJAX_OPTION_JUMP" => "N",
                        "AJAX_OPTION_STYLE" => "Y",
                        "CACHE_FILTER" => "N",
                        "CACHE_GROUPS" => "Y",
                        "CACHE_TIME" => "36000000",
                        "CACHE_TYPE" => "A",
                        "CHECK_DATES" => "Y",
                        "DETAIL_URL" => "",
                        "DISPLAY_BOTTOM_PAGER" => "N",
                        "DISPLAY_DATE" => "Y",
                        "DISPLAY_NAME" => "N",
                        "DISPLAY_PICTURE" => "Y",
                        "DISPLAY_PREVIEW_TEXT" => "Y",
                        "DISPLAY_TOP_PAGER" => "N",
                        "FIELD_CODE" => array(),
                        "FILTER_NAME" => '',
                        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                        "IBLOCK_ID" => IblockHelper::getId('MENU'),
                        "IBLOCK_TYPE" => 'content',
                        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                        "INCLUDE_SUBSECTIONS" => "Y",
                        "MESSAGE_404" => "",
                        "NEWS_COUNT" => "50",
                        "PAGER_BASE_LINK_ENABLE" => "N",
                        "PAGER_DESC_NUMBERING" => "N",
                        "PAGER_DESC_NUMBERING_CACHE_TIME" => "3600000000",
                        "PAGER_SHOW_ALL" => "N",
                        "PAGER_SHOW_ALWAYS" => "N",
                        "PAGER_TEMPLATE" => ".default",
                        "PAGER_TITLE" => "Новости",
                        "PARENT_SECTION" => "",
                        "PARENT_SECTION_CODE" => "footer",
                        "PREVIEW_TRUNCATE_LEN" => "",
                        "PROPERTY_CODE" => array('LINK'),
                        "SET_BROWSER_TITLE" => "N",
                        "SET_LAST_MODIFIED" => "N",
                        "SET_META_DESCRIPTION" => "N",
                        "SET_META_KEYWORDS" => "N",
                        "SET_STATUS_404" => "N",
                        "SET_TITLE" => "N",
                        "SHOW_404" => "N",
                        "SORT_BY1" => "SORT",
                        "SORT_BY2" => "ACTIVE_FROM",
                        "SORT_ORDER1" => "ASC",
                        "SORT_ORDER2" => "DESC",
                        "STRICT_SECTION_CHECK" => "N",
                        'SELECTED_CATALOG' => $selectedCatalog
                    )
                );
                ?>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <?php $APPLICATION->IncludeComponent('imedia:html.include', 'footer-payments', [], false, ['HIDE_ICONS' => true])?>
            <div class="footer__developer"><?=Loc::getMessage('T_FOOTER_DEVELOPER')?></div>
            <div class="footer__copy"><?=Loc::getMessage('T_FOOTER_COPYRIGHT', ['#YEAR#' => date('Y')])?></div>
        </div>
    </div>
</footer>
<?php if(!Common::isBot()): ?>
    <button class="pageup active" type="button" aria-label="<?=Loc::getMessage('T_FOOTER_TO_TOP')?>">
        <svg class="pageup__arrow" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M23.5254 11.9626C23.5254 12.2001 23.4379 12.4376 23.2504 12.6251C22.8879 12.9876 22.2879 12.9876 21.9254 12.6251L15.0004 5.7001L8.07539 12.6251C7.71289 12.9876 7.11289 12.9876 6.75039 12.6251C6.38789 12.2626 6.38789 11.6626 6.75039 11.3001L14.3379 3.7126C14.7004 3.3501 15.3004 3.3501 15.6629 3.7126L23.2504 11.3001C23.4379 11.4876 23.5254 11.7251 23.5254 11.9626Z" fill="#505661"></path>
            <path d="M15.9375 4.58775L15.9375 25.6252C15.9375 26.1377 15.5125 26.5627 15 26.5627C14.4875 26.5627 14.0625 26.1377 14.0625 25.6252L14.0625 4.58775C14.0625 4.07525 14.4875 3.65025 15 3.65025C15.5125 3.65025 15.9375 4.07525 15.9375 4.58775Z" fill="#505661"></path>
        </svg>
    </button>
    <cart></cart>
    <product-fast-view></product-fast-view>
    <notifications group="foo" animation-type="velocity" position="top right" class="notifications"></notifications>
    <?php if(!($currentUser->getId() > 0)): ?>
        <auth-popup></auth-popup>
    <?php endif ?>
    <?php
    $APPLICATION->IncludeComponent(
        'imedia:popup.couponforsubscribe',
        '',
        [],
        false,
        ['HIDE_ICONS' => true]
    );
    ?>
<?php endif ?>
</div>
<?php $APPLICATION->IncludeFile('/local/include/'.SITE_ID.'/body-end.php', [], ['MODE' => 'html']);?>
</body>
</html>