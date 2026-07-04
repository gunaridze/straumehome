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
<?php if(!empty($arParams['SELECTED_TAGS'])): ?>
    <div class="blog-tags">
        <?php foreach($arParams['SELECTED_TAGS'] as $tag):?>
            <a href="<?=$arParams['FOLDER']?>" class="blog-tag" title="<?=$tag?>">
                #<?=$tag?>
                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.9929 3L8 6.99286L4.00714 3L3 4.00714L6.99286 8L3 11.9929L4.00714 13L8 9.00714L11.9929 13L13 11.9929L9.00714 8L13 4.00714L11.9929 3Z" fill="#9b9b9b"></path>
                </svg>
            </a>
        <?php endforeach?>
    </div>
<?php endif;