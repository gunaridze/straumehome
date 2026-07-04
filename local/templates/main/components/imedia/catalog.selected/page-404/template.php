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
<nav class="breadcrumbs">
    <ul class="breadcrumbs__list">
        <li class="breadcrumbs__item">
            <a href="<?=SITE_DIR?>" title="<?=Loc::getMessage('T_CATALOG_SELECTED_404_LINK_MAINPAGE')?>">
                <?=Loc::getMessage('T_CATALOG_SELECTED_404_LINK_MAINPAGE')?>
            </a>
        </li>
        <?php foreach($arResult['SECTIONS'] as $arSection):?>
            <li class="breadcrumbs__item">
                <a href="<?=$arSection['LINK']?>" title="<?=$arSection['NAME']?>">
                    <?=$arSection['NAME']?>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
</nav>
