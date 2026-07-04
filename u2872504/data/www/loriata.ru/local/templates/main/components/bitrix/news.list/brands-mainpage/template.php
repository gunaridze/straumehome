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

use \Bitrix\Main\Localization\Loc;

$link = str_replace(['#SITE_DIR#', '//'], [SITE_DIR, '/'], $arResult['LIST_PAGE_URL']);
$link .= $arParams['SELECTED_SECTION_PATH'] . '/';
?>
<?php if(!empty($arResult['ITEMS'])):?>
    <section class="section brands">
        <div class="container">
            <div class="section__title">
                <div class="title"><?=Loc::getMessage('T_BRANDS_MAINPAGE_TITLE')?></div>
                <a href="<?=$link?>" class="all-link" title="<?=Loc::getMessage('T_BRANDS_MAINPAGE_LINK')?>">
                    <?=Loc::getMessage('T_BRANDS_MAINPAGE_LINK')?>
                    <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M55.7071 8.70711C56.0976 8.31658 56.0976 7.68342 55.7071 7.29289L49.3431 0.928932C48.9526 0.538408 48.3195 0.538408 47.9289 0.928932C47.5384 1.31946 47.5384 1.95262 47.9289 2.34315L53.5858 8L47.9289 13.6569C47.5384 14.0474 47.5384 14.6805 47.9289 15.0711C48.3195 15.4616 48.9526 15.4616 49.3431 15.0711L55.7071 8.70711ZM0 9H55V7H0V9Z" fill="#C0C0C0"></path>
                    </svg>
                </a>
            </div>
            <div class="brands__inner">
                <?php foreach($arResult['ITEMS'] as $arItem): ?>
                    <a
                        href="<?=$arItem['DETAIL_PAGE_URL']?>"
                        class="brands__item animate__ animate__fadeIn wow"
                        data-wow-duration="1s"
                        title="<?=$arItem['NAME']?>"
                    >
                        <picture>
                            <img
                                class="brands__item-img"
                                src="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                                alt="<?=$arItem['PREVIEW_PICTURE']['ALT']?>"
                                width="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                                height="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                                srcset="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                                loading="lazy"
                            >
                        </picture>
                    </a>
                <?php endforeach ?>
            </div>
        </div>
    </section>
<?php endif;