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
<?php foreach($arResult["ITEMS"] as $arItem):
    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
    ?>
    <div class="blog-text__slider swiper" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
        <div class="swiper-wrapper">
            <?php foreach($arItem['DISPLAY_PROPERTIES']['GALLERY']['FILE_VALUE']['RESIZE'] as $arImage):
                ?>
                <div class="swiper-slide">
                    <picture>
                        <img
                            src="<?=$arImage['SIZES']['DEFAULT']?>"
                            alt="<?=$arImage['META']['ALT']?>"
                            width="<?=$arImage['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                            height="<?=$arImage['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                            srcset="<?=$arImage['SIZES']['DEFAULT_2X']?> 2x"
                            loading="lazy"
                        >
                    </picture>
                </div>
            <?php endforeach?>
        </div>
        <div class="swiper-button-prev">
            <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.3426 21.4595C11.5504 21.4595 11.7582 21.3829 11.9223 21.2188C12.2395 20.9017 12.2395 20.3767 11.9223 20.0595L5.86289 14.0001L11.9223 7.94072C12.2395 7.62354 12.2395 7.09854 11.9223 6.78135C11.6051 6.46416 11.0801 6.46416 10.7629 6.78135L4.12382 13.4204C3.80664 13.7376 3.80664 14.2626 4.12382 14.5798L10.7629 21.2188C10.927 21.3829 11.1348 21.4595 11.3426 21.4595Z" fill="#101112" />
                <path d="M4.88906 14.8203H23.2969C23.7453 14.8203 24.1172 14.4484 24.1172 14C24.1172 13.5516 23.7453 13.1797 23.2969 13.1797H4.88906C4.44062 13.1797 4.06875 13.5516 4.06875 14C4.06875 14.4484 4.44062 14.8203 4.88906 14.8203Z" fill="#101112" />
            </svg>
        </div>
        <div class="swiper-button-next">
            <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16.6574 21.4595C16.4496 21.4595 16.2418 21.3829 16.0777 21.2188C15.7605 20.9017 15.7605 20.3767 16.0777 20.0595L22.1371 14.0001L16.0777 7.94072C15.7605 7.62354 15.7605 7.09854 16.0777 6.78135C16.3949 6.46416 16.9199 6.46416 17.2371 6.78135L23.8762 13.4204C24.1934 13.7376 24.1934 14.2626 23.8762 14.5798L17.2371 21.2188C17.073 21.3829 16.8652 21.4595 16.6574 21.4595Z" fill="#101112" />
                <path d="M23.1109 14.8203H4.70312C4.25469 14.8203 3.88281 14.4484 3.88281 14C3.88281 13.5516 4.25469 13.1797 4.70312 13.1797H23.1109C23.5594 13.1797 23.9312 13.5516 23.9312 14C23.9312 14.4484 23.5594 14.8203 23.1109 14.8203Z" fill="#101112" />
            </svg>
        </div>
    </div>
<?php endforeach;