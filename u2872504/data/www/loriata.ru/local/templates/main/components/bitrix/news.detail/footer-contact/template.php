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
<?php if(
    $arResult['PROPERTIES']['PHONE']['VALUE']
    || $arResult['PROPERTIES']['HOURS']['VALUE']
): ?>
<div class="footer__contact">
    <?php if($arResult['PROPERTIES']['PHONE']['VALUE']):
        $phone = preg_replace('/[^\d+]/', '', $arResult['PROPERTIES']['PHONE']['VALUE']);
        ?>
        <a
            href="tel:<?=$phone?>"
            class="footer__phone"
            title="<?=$arResult['PROPERTIES']['PHONE']['VALUE']?>"
            target="_blank"
            rel="nofollow"
        ><?=$arResult['PROPERTIES']['PHONE']['VALUE']?></a>
    <?php endif ?>
    <?php if($arResult['PROPERTIES']['HOURS']['VALUE']): ?>
        <div class="footer__contact-descr"><?=$arResult['PROPERTIES']['HOURS']['VALUE']?></div>
    <?php endif ?>
</div>
<?php endif;