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
<?php if(!empty($arResult['ITEMS'])): ?>
    <div class="brands-wrap">
        <div class="brands-index-list">
            <?php foreach($arResult['ITEMS'] as $arItem): ?>
                <a
                    href="<?=$arItem['LINK']?>"
                    class="brands-index-list__item<?=($arItem['SELECTED']) ? ' brands-index-list__item--selected' : ''?>"
                    title="<?=$arItem['VALUE']?>"
                ><?=$arItem['VALUE']?></a>
            <?php endforeach ?>
        </div>
    </div>
<?php endif;
