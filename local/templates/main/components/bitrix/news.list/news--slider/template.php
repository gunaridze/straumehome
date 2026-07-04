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

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);
?>
<?php if(!empty($arResult['ITEMS'])): ?>
<section class="other-news">
    <div class="other-news__top">
        <div class="title"><?=Loc::getMessage('T_NEWS_SLIDER_TITLE')?></div>
        <div class="slider-arrows">
            <div class="swiper-button-prev other-news__slider-arrow--prev">
                <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.292892 8.70711C-0.0976295 8.31658 -0.0976295 7.68342 0.292892 7.29289L6.65685 0.928932C7.04738 0.538408 7.68054 0.538408 8.07107 0.928932C8.46159 1.31946 8.46159 1.95262 8.07107 2.34315L2.41422 8L8.07107 13.6569C8.46159 14.0474 8.46159 14.6805 8.07107 15.0711C7.68054 15.4616 7.04738 15.4616 6.65685 15.0711L0.292892 8.70711ZM56 9H1V7H56V9Z" fill="#C0C0C0" />
                </svg>
            </div>
            <div class="swiper-button-next other-news__slider-arrow--next">
                <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M55.7071 8.70711C56.0976 8.31658 56.0976 7.68342 55.7071 7.29289L49.3431 0.928932C48.9526 0.538408 48.3195 0.538408 47.9289 0.928932C47.5384 1.31946 47.5384 1.95262 47.9289 2.34315L53.5858 8L47.9289 13.6569C47.5384 14.0474 47.5384 14.6805 47.9289 15.0711C48.3195 15.4616 48.9526 15.4616 49.3431 15.0711L55.7071 8.70711ZM0 9H55V7H0V9Z" fill="#C0C0C0" />
                </svg>
            </div>
        </div>
    </div>
    <div class="swiper other-news__slider">
        <div class="swiper-wrapper">
            <?php foreach($arResult["ITEMS"] as $arItem):
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                $objDate = new \Bitrix\Main\Type\DateTime($arItem['ACTIVE_FROM'], 'd.m.Y H:i:s');
                ?>
                <article class="blog-item swiper-slide" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                    <?php if($arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']):?>
                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="blog-item__img" title="<?=$arItem['NAME']?>">
                            <picture>
                                <img
                                        src="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                                        alt="<?=$arItem['PREVIEW_PICTURE']['ALT']?>"
                                        width="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                                        height="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                                        srcset="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                                        loading="lazy"
                                >
                            </picture>
                        </a>
                    <?php endif?>
                    <div class="blog-item__content">
                        <time class="blog-item__date" datetime="<?=$objDate->format('Y-m-d')?>"
                        ><?=$objDate->format('d.m.Y')?></time>
                        <a
                            class="blog-item-title blog-item__title"
                            href="<?=$arItem['DETAIL_PAGE_URL']?>"
                            title="<?=$arItem['NAME']?>"
                        ><?=$arItem['NAME']?></a>
                        <?php if($arItem['PREVIEW_TEXT']):?>
                            <p class="blog-item-text blog-item__text"><?=$arItem['PREVIEW_TEXT']?></p>
                        <?php endif?>
                        <?php if(!empty($arItem['TAGS'])):?>
                            <div class="tags">
                                <?php foreach($arItem['TAGS'] as $arTag):?>
                                    <a
                                            class="tag blog-item__tag"
                                            href="<?=$arTag['LINK']?>"
                                            title="<?=$arTag['LABEL']?>"
                                    >#<?=$arTag['LABEL']?></a>
                                <?php endforeach?>
                            </div>
                        <?php endif?>
                    </div>
                </article>
            <?php endforeach?>
        </div>
    </div>
</section>
<?php endif;