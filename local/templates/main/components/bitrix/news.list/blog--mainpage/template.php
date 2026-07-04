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

$linkList = str_replace(['#SITE_DIR#', '//'], [SITE_DIR, '/'], $arResult['LIST_PAGE_URL']);
$link = $linkList . '?tags=' . mb_strtolower($arParams['SELECTED_SECTION_NAME']);
?>
<?php if(!empty($arResult['ITEMS'])):?>
    <section class="section blog-section">
        <div class="container">
            <div class="section__title">
                <div class="title"><?=Loc::getMessage('T_BLOG_MAINPAGE_TITLE')?></div>
                <a href="<?=$link?>" class="all-link" title="<?=Loc::getMessage('T_BLOG_MAINPAGE_LINK')?>">
                    <?=Loc::getMessage('T_BLOG_MAINPAGE_LINK')?>
                    <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M55.7071 8.70711C56.0976 8.31658 56.0976 7.68342 55.7071 7.29289L49.3431 0.928932C48.9526 0.538408 48.3195 0.538408 47.9289 0.928932C47.5384 1.31946 47.5384 1.95262 47.9289 2.34315L53.5858 8L47.9289 13.6569C47.5384 14.0474 47.5384 14.6805 47.9289 15.0711C48.3195 15.4616 48.9526 15.4616 49.3431 15.0711L55.7071 8.70711ZM0 9H55V7H0V9Z" fill="#C0C0C0"></path>
                    </svg>
                </a>
            </div>
            <div class="blog-grid">
                <?php
                $delay = 0;
                $delayStep = .5;
                foreach($arResult['ITEMS'] as $key => $arItem):
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), ["CONFIRM" => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);

                    $animateClass = ($key > 0) ? 'animate__fadeInRight' : 'animate__fadeInLeft';

                    $objDate = new \Bitrix\Main\Type\DateTime($arItem['ACTIVE_FROM'], 'd.m.Y H:i:s');
                    ?>
                    <article
                        class="blog-item blog-grid__item animate__ <?=$animateClass?> wow"
                        data-wow-duration="1.5s"
                        <?php if($delay > 0): ?>
                            data-wow-delay="<?=$delay?>s"
                        <?php endif ?>
                        id="<?=$this->GetEditAreaId($arItem['ID']);?>"
                    >
                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="blog-item__img" title="<?=$arItem['NAME']?>">
                            <img
                                class="loaded"
                                src="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                                alt="<?=$arItem['PREVIEW_PICTURE']['ALT']?>"
                                width="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                                height="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                                srcset="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                                loading="lazy"
                            >
                        </a>
                        <div class="blog-item__content">
                            <time
                                class="date blog-item__date"
                                datetime="<?=$objDate->format('Y-m-d')?>"
                            ><?=$objDate->format('d.m.Y')?></time>
                            <a
                                class="blog-item-title blog-item__title"
                                href="<?=$arItem['DETAIL_PAGE_URL']?>"
                                title="<?=$arItem['NAME']?>"
                            ><?=$arItem['NAME']?></a>
                            <?php if($arItem['PREVIEW_TEXT']): ?>
                                <p class="blog-item-text blog-item__text"><?=$arItem['PREVIEW_TEXT']?></p>
                            <?php endif ?>
                            <?php if(!empty($arItem['TAGS'])): ?>
                                <div class="tags">
                                    <?php foreach($arItem['TAGS'] as $tag): ?>
                                        <a
                                            href="<?=$linkList . '?tags=' . $tag?>"
                                            class="tag blog-item__tag"
                                            title="<?=Loc::getMessage('T_BLOG_MAINPAGE_TITLE')?>: <?=$tag?>"
                                        >#<?=$tag?></a>
                                    <?php endforeach ?>
                                </div>
                            <?php endif ?>
                        </div>
                    </article>
                <?php
                $delay += $delayStep;
                endforeach ?>
            </div>
        </div>
    </section>
<?php endif;