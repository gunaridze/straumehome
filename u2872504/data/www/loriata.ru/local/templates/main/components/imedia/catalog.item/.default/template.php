<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);

?>
<?php if(!empty($arResult)): ?>
    <div class="catalog-card">
        <?php if(!empty($arResult['labels'])):?>
            <div class="labels">
                <?php foreach($arResult['labels'] as $label):?>
                    <span class="label catalog-card__label label--<?=$label['code']?>"><?=$label['label']?></span>
                <?php endforeach?>
            </div>
        <?php endif?>
        <a class="catalog-card__img" href="<?=$arResult['url']?>" title="<?=$arResult['name']?>">
            <?php if($arResult['picture']): ?>
                <picture>
                    <img
                        src="<?=$arResult['picture']['src']?>"
                        srcset="<?=$arResult['picture']['src2x']?> 2x"
                        alt="<?=$arResult['name']?>"
                        loading="lazy"
                    >
                </picture>
            <?php endif ?>
            <div class="catalog-card__info">
                <div class="catalog-card__sizes">
                    <?php if(!empty($arResult['offers'])): ?>
                        <?php foreach($arResult['offers'] as $offer): ?>
                            <div
                                class="catalog-card__size<?=(!$offer['canBuy']) ? ' catalog-card__size--disabled' : ''?>"
                            ><?=$offer['size']?></div>
                        <?php endforeach ?>
                    <?php elseif($arResult['size']):?>
                        <div
                            class="catalog-card__size<?=(!$arResult['canBuy']) ? ' catalog-card__size--disabled' : ''?>"
                        ><?=$arResult['size']?></div>
                    <?php endif ?>
                </div>
                <button
                    data-fast-view="<?=$arResult['ID']?>"
                    class="catalog-card__info-link"
                    aria-label="<?=Loc::getMessage('T_IMEDIA_CATALOG_SECTION_DEFAULT_FAST_VIEW')?>"
                ><?=Loc::getMessage('T_IMEDIA_CATALOG_SECTION_DEFAULT_FAST_VIEW')?></button>
            </div>
        </a>
        <a
            class="catalog-card__title"
            href="<?=$arResult['url']?>"
            title="<?=$arResult['name']?>"
        ><?=$arResult['name']?></a>
        <?php if($arResult['brand']): ?>
            <div class="catalog-card__subtitle"><?=$arResult['brand']?></div>
        <?php endif ?>
        <?php if($arResult['price']['discount']['raw'] > 0): ?>
            <div class="prices">
                <div class="new-price catalog-card__new-price"><?=$arResult['price']['result']['formatted']?></div>
                <div class="old-price catalog-card__old-price"><?=$arResult['price']['base']['formatted']?></div>
            </div>
        <?php else: ?>
            <div class="catalog-card__price"><?=$arResult['price']['result']['formatted']?></div>
        <?php endif ?>
    </div>
<?php endif;