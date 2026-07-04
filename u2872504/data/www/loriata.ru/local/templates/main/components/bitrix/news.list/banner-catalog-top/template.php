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
    <?php foreach($arResult['ITEMS'] as $arItem):
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), ["CONFIRM" => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);
        ?>
        <article class="promo-collection" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
            <div class="container">
                <div class="promo-collection__inner">
                    <?php if(!empty($arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT'])):?>
                        <div class="promo-collection__img">
                            <picture>
                                <img
                                        src="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                                        alt="<?=$arItem['PREVIEW_PICTURE']['ALT']?>"
                                        width="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                                        height="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                                        srcset="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                                >
                            </picture>
                        </div>
                    <?php endif?>
                    <?php if($arItem['DISPLAY_PROPERTIES']['SUBTITLE']['DISPLAY_VALUE']):?>
                        <div class="promo-collection__suptitle"><?=$arItem['DISPLAY_PROPERTIES']['SUBTITLE']['DISPLAY_VALUE']?></div>
                    <?php endif?>
                    <div class="title promo-collection__title"><?=($arItem['DISPLAY_PROPERTIES']['TITLE']['DISPLAY_VALUE']) ?: $arItem['NAME']?></div>
                    <?php if($arItem['PROPERTIES']['LINK']['VALUE']):?>
                        <a
                                href="<?=$arItem['PROPERTIES']['LINK']['VALUE']?>"
                                class="all-link promo-collection__link"
                                title="<?=$arItem['NAME']?>"
                        >
                            <?=($arItem['PROPERTIES']['LINK_TITLE']['VALUE']) ?: Loc::getMessage('T_BANNER_CATALOG_TOP_LINK')?>
                            <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M55.7071 8.70711C56.0976 8.31658 56.0976 7.68342 55.7071 7.29289L49.3431 0.928932C48.9526 0.538408 48.3195 0.538408 47.9289 0.928932C47.5384 1.31946 47.5384 1.95262 47.9289 2.34315L53.5858 8L47.9289 13.6569C47.5384 14.0474 47.5384 14.6805 47.9289 15.0711C48.3195 15.4616 48.9526 15.4616 49.3431 15.0711L55.7071 8.70711ZM0 9H55V7H0V9Z" fill="#C0C0C0"></path>
                            </svg>
                        </a>
                    <?php endif?>
                </div>
            </div>
        </article>
    <?php endforeach ?>
<?php endif ?>