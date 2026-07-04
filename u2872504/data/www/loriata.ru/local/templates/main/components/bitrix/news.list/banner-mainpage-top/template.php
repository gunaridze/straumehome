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
        <article class="sale-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
            <a
                href="<?=$arItem['PROPERTIES']['LINK']['VALUE']?>"
                title="<?=$arItem['NAME']?>"
                class="sale-item__img"
            >
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
                <?php if($arItem['PROPERTIES']['LABEL']['VALUE']): ?>
                    <span class="sale-item__discount"><?=$arItem['PROPERTIES']['LABEL']['VALUE']?></span>
                <?php endif ?>
            </a>
            <?php if($arItem['PROPERTIES']['TITLE']['VALUE']): ?>
                <a
                    class="sale-item__title"
                    href="<?=$arItem['PROPERTIES']['LINK']['VALUE']?>"
                    title="<?=$arItem['NAME']?>"
                ><?=$arItem['PROPERTIES']['TITLE']['VALUE']?></a>
            <?php endif ?>
            <?php if($arItem['PROPERTIES']['SUBTITLE']['VALUE']): ?>
                <div class="sale-item__descr"><?=$arItem['PROPERTIES']['SUBTITLE']['VALUE']?></div>
            <?php endif ?>
        </article>
    <?php endforeach ?>
<?php endif;