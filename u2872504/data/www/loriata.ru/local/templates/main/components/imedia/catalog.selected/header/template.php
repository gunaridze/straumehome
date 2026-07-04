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
<?php if(!empty($arResult['SECTIONS'])): ?>
    <nav class="header__nav">
        <?php foreach($arResult['SECTIONS'] as $arSection):?>
            <a
                href="<?=$arSection['LINK']?>"
                class="header__nav-link<?=($arSection['SELECTED']) ? ' active' : ''?>"
                title="<?=$arSection['NAME']?>"
            ><?=$arSection['NAME']?></a>
        <?php endforeach ?>
    </nav>
<?php endif;
