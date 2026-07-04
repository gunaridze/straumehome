<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
?>
<div class="blog-text__content">
    <?php if(!empty($arResult['TAGS'])):?>
        <div class="tags blog-text__tags">
            <?php foreach($arResult['TAGS'] as $arTag):?>
                <a
                        class="tag blog-text__tag"
                        href="<?=$arTag['LINK']?>"
                        title="<?=$arTag['LABEL']?>"
                >#<?=$arTag['LABEL']?></a>
            <?php endforeach?>
        </div>
    <?php endif?>
    <?php if($arResult['DETAIL_TEXT']):?>
        <div class="blog-text__text"><?=$arResult['DETAIL_TEXT']?></div>
    <?php endif?>
</div>