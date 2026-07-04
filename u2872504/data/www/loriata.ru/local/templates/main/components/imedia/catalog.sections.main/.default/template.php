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
    <div class="catalog-home__grid">
        <?php foreach($arResult as $arSection):

            $mobilePicture = ($arSection['DETAIL_PICTURE']['RESIZE'][0]['SIZES'])
                ?: $arSection['PICTURE']['RESIZE'][0]['SIZES'];

            ?>
            <a
                class="catalog-home-card"
                href="<?=$arSection['LINK']?>"
                title="<?=$arSection['NAME']?>"
            >
                <picture class="catalog-home-card__img">
                    <source
                        media="(min-width: 501px)"
                        srcset="<?=$arSection['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?> 1x, <?=$arSection['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                    >
                    <img
                        src="<?=$mobilePicture['DEFAULT']?>"
                        alt="<?=$arSection['PICTURE']['ALT']?>"
                        srcset="<?=$mobilePicture['DEFAULT_2X']?> 2x"
                    >
                </picture>
                <div class="catalog-home-card__title"><?=$arSection['NAME']?></div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif;