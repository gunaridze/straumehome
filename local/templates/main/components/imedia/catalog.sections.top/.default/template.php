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
?>
<?php if(!empty($arResult)): ?>
    <div class="section">
        <div class="container">
            <div class="icons-slider swiper">
                <div class="swiper-wrapper">
                    <?php foreach($arResult as $arSection): ?>
                        <a
                            href="<?=$arSection['LINK']?>"
                            class="swiper-slide icons-slider__item animate__animated animate__fadeIn wow"
                            data-wow-duration="1s"
                            title="<?=$arSection['NAME']?>"
                        >
                            <div class="icons-slider__item-img">
                                <picture>
                                    <img
                                        src="<?=$arSection['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                                        alt="<?=$arSection['PICTURE']['ALT']?>"
                                        width="<?=$arSection['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                                        height="<?=$arSection['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                                        srcset="<?=$arSection['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                                    >
                                </picture>
                            </div>
                            <div class="icons-slider__item-title"><?=$arSection['NAME']?></div>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
<?php endif;
