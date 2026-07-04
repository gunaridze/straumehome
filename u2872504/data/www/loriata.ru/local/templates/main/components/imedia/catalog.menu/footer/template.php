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
    <div class="footer__menu">
        <?php foreach($arResult as $arItem): ?>
            <a
                href="<?=$arItem['LINK']?>"
                title="<?=$arItem['NAME']?>"
                class="footer__link<?=($arItem['IS_PRIMARY']) ? ' footer__link--red' : ''?>"
            ><?=$arItem['NAME']?></a>
        <?php endforeach ?>
    </div>
<?php endif;