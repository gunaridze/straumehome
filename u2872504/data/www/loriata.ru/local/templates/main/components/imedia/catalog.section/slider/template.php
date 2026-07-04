<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

$this->setFrameMode(true);
?>
<?php if(!empty($arResult['ITEMS'])): ?>
    <products-slider
        class="section slider-section"
        :items='<?=Json::encode($arResult['ITEMS'])?>'
        slider-title="<?=$arParams['SLIDER_TITLE']?>"
        slider-id="<?=$arParams['SLIDER_ID']?>"
    >
        <div class="container">
            <div class="section__title">
                <div class="title"><?=$arParams['SLIDER_TITLE']?></div>
            </div>
            <div class="slider-arrows">
                <div class="swiper-button-prev <?=$arParams['SLIDER_ID']?>-arrow-prev">
                    <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.292892 8.70711C-0.0976295 8.31658 -0.0976295 7.68342 0.292892 7.29289L6.65685 0.928932C7.04738 0.538408 7.68054 0.538408 8.07107 0.928932C8.46159 1.31946 8.46159 1.95262 8.07107 2.34315L2.41422 8L8.07107 13.6569C8.46159 14.0474 8.46159 14.6805 8.07107 15.0711C7.68054 15.4616 7.04738 15.4616 6.65685 15.0711L0.292892 8.70711ZM56 9H1V7H56V9Z" fill="#C0C0C0" />
                    </svg>
                </div>
                <div class="swiper-button-next <?=$arParams['SLIDER_ID']?>-arrow-next">
                    <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M55.7071 8.70711C56.0976 8.31658 56.0976 7.68342 55.7071 7.29289L49.3431 0.928932C48.9526 0.538408 48.3195 0.538408 47.9289 0.928932C47.5384 1.31946 47.5384 1.95262 47.9289 2.34315L53.5858 8L47.9289 13.6569C47.5384 14.0474 47.5384 14.6805 47.9289 15.0711C48.3195 15.4616 48.9526 15.4616 49.3431 15.0711L55.7071 8.70711ZM0 9H55V7H0V9Z" fill="#C0C0C0" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="swiper <?=$arParams['SLIDER_ID']?>">
            <?php foreach($arResult['ITEMS'] as $arItem): ?>
                <div class="swiper-wrapper">
                    <?php $APPLICATION->IncludeComponent(
                        'imedia:catalog.item',
                        '',
                        [
                            'ITEM' => $arItem
                        ],
                        $this->component,
                        ['HIDE_ICONS' => true]
                    ) ?>
                </div>
            <?php endforeach ?>
        </div>
    </products-slider>
<?php endif;
