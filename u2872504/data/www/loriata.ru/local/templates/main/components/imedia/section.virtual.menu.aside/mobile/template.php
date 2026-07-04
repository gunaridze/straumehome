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
<div class="catalog-dropdown">
    <?php if(empty($arResult['SELECTED_ITEM']['ITEMS'])):?>
        <a
            class="catalog-dropdown__title active"
            href="<?=$arResult['SELECTED_ITEM']['PARENT']['LINK']?>"
            title="<?=$arResult['SELECTED_ITEM']['PARENT']['NAME']?>"
        >
            <span><?=$arResult['SELECTED_ITEM']['NAME']?></span>
        </a>
    <?php else:?>
        <div class="catalog-dropdown__title">
            <span><?=$arResult['SELECTED_ITEM']['NAME']?></span>
            <ul class="catalog-dropdown__content">
                <?php foreach($arResult['SELECTED_ITEM']['ITEMS'] as $arItem):?>
                    <li>
                        <a
                            href="<?=$arItem['LINK']?>"
                            title="<?=$arItem['NAME']?>"
                        ><?=$arItem['NAME']?></a>
                    </li>
                <?php endforeach?>
            </ul>
        </div>
    <?php endif?>
</div>
