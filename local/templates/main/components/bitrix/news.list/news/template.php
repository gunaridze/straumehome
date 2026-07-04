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
?>
<?php if(!empty($arResult['ITEMS'])): ?>
    <div class="news-page__grid">
        <?php foreach($arResult["ITEMS"] as $arItem):
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

            $objDate = new \Bitrix\Main\Type\DateTime($arItem['ACTIVE_FROM'], 'd.m.Y H:i:s');
            ?>
            <article class="blog-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                <?php if($arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']):?>
                    <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="blog-item__img" title="<?=$arItem['NAME']?>">
                        <picture>
                            <img
                                src="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                                alt="<?=$arItem['PREVIEW_PICTURE']['ALT']?>"
                                width="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                                height="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                                srcset="<?=$arItem['PREVIEW_PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                                loading="lazy"
                            >
                        </picture>
                    </a>
                <?php endif?>
                <div class="blog-item__content">
                    <time class="blog-item__date" datetime="<?=$objDate->format('Y-m-d')?>"
                    ><?=$objDate->format('d.m.Y')?></time>
                    <a class="blog-item-title blog-item__title" href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem['NAME']?></a>
                    <?php if($arItem['PREVIEW_TEXT']):?>
                        <p class="blog-item-text blog-item__text"><?=$arItem['PREVIEW_TEXT']?></p>
                    <?php endif?>
                    <?php if(!empty($arItem['TAGS'])):?>
                        <div class="tags">
                            <?php foreach($arItem['TAGS'] as $arTag):?>
                                <a
                                    class="tag blog-item__tag"
                                    href="<?=$arTag['LINK']?>"
                                    title="<?=$arTag['LABEL']?>"
                                >#<?=$arTag['LABEL']?></a>
                            <?php endforeach?>
                        </div>
                    <?php endif?>
                </div>
            </article>
        <?php endforeach?>
    </div>
    <nav class="pagination">
        <?=$arResult["NAV_STRING"]?>
    </nav>
<?php endif;
