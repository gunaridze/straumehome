<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
?>
<section class="news-detail">
    <div class="news-detail__inner">
        <?php if($arResult['DETAIL_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']):?>
            <div class="news-detail__img">
                <picture>
                    <img
                        src="<?=$arResult['DETAIL_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                        alt="<?=$arResult['DETAIL_PICTURE']['ALT']?>"
                        width="<?=$arResult['DETAIL_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                        height="<?=$arResult['DETAIL_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                        srcset="<?=$arResult['DETAIL_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                        loading="lazy"
                    >
                </picture>
            </div>
        <?php endif?>
        <div class="news-detail__content"><?=$arResult['DETAIL_TEXT']?></div>
    </div>
</section>