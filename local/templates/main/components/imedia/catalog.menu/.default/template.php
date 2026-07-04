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
<?php ob_start(); ?>
<ul class="header__list">
    <?php if(!empty($arResult['ITEMS'])): ?>
        <?php foreach($arResult['ITEMS'] as $arItem):
            $itemId = $arItem['CODE'] . '-' . $arItem['ID'];
            ?>
            <li class="header__list-item">
                <a
                    href="<?=$arItem['LINK']?>"
                    class="header__list-link<?=($arItem['IS_PRIMARY']) ? ' header__list-link--red' : ''?>"
                    <?php if(!empty($arItem['ITEMS'])): ?>
                        data-category-link="<?=$itemId?>"
                    <?php endif ?>
                ><?=$arItem['NAME']?></a>
                <?php if(!empty($arItem['ITEMS'])): ?>
                    <div class="drop-menu" id="<?=$itemId?>">
                        <div class="drop-menu__row">
                            <?php if(!empty($arItem['ITEMS']['SECTIONS'])):?>
                                <?php foreach($arItem['ITEMS']['SECTIONS'] as $key => $arSection):?>
                                    <div class="drop-menu__col drop-menu__col--<?=($key + 1)?>">
                                        <a
                                            class="drop-menu__col-title"
                                            title="<?=$arSection['NAME']?>"
                                            href="<?=$arSection['LINK']?>"
                                        ><?=$arSection['NAME']?></a>
                                        <ul class="drop-menu__list">
                                            <?php foreach($arSection['ITEMS'] as $arLink):?>
                                                <li class="drop-menu__list-item">
                                                    <a
                                                        href="<?=$arLink['LINK']?>"
                                                        class="drop-menu__list-link"
                                                        title="<?=$arLink['NAME']?>"
                                                    ><?=$arLink['NAME']?></a>
                                                </li>
                                            <?php endforeach?>
                                            <li class="drop-menu__list-item">
                                                <a
                                                    href="<?=$arSection['LINK']?>"
                                                    class="drop-menu__list-link"
                                                    title="<?=$arSection['NAME']?>"
                                                ><?=Loc::getMessage('T_CATALOG_MENU_LINK_ALL')?></a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endforeach?>
                            <?php else:?>
                                <?php $colNum = 0; ?>
                                <?php if(!empty($arItem['ITEMS']['MAIN'])): ?>
                                    <?php $colNum++; ?>
                                    <div class="drop-menu__col drop-menu__col--<?=$colNum?>">
                                        <div class="drop-menu__col-title"><?=$arItem['TITLE_PLURAL']?></div>
                                        <ul class="drop-menu__list">
                                            <?php foreach($arItem['ITEMS']['MAIN'] as $arLink): ?>
                                                <li class="drop-menu__list-item">
                                                    <a
                                                        href="<?=$arLink['LINK']?>"
                                                        class="drop-menu__list-link"
                                                        title="<?=$arLink['NAME']?>"
                                                    ><?=$arLink['NAME']?></a>
                                                </li>
                                            <?php endforeach ?>
                                            <li class="drop-menu__list-item">
                                                <a
                                                    href="<?=$arItem['LINK']?>"
                                                    class="drop-menu__list-link"
                                                    title="<?=$arItem['NAME']?>"
                                                ><?=Loc::getMessage('T_CATALOG_MENU_LINK_ALL')?></a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif ?>
                                <?php if(!empty($arItem['ITEMS']['POPULAR'])): ?>
                                    <?php $colNum++; ?>
                                    <div class="drop-menu__col drop-menu__col--<?=$colNum?>">
                                        <div class="drop-menu__col-title"><?=Loc::getMessage('T_CATALOG_MENU_TITLE_POPULAR')?></div>
                                        <ul class="drop-menu__list">
                                            <?php foreach($arItem['ITEMS']['POPULAR'] as $arLink): ?>
                                                <li class="drop-menu__list-item">
                                                    <a
                                                        href="<?=$arLink['LINK']?>"
                                                        class="drop-menu__list-link"
                                                        title="<?=$arLink['NAME']?>"
                                                    ><?=$arLink['NAME']?></a>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                <?php endif ?>
                                <?php if(!empty($arItem['ITEMS']['BRANDS'])): ?>
                                    <?php $colNum++; ?>
                                    <div class="drop-menu__col drop-menu__col--<?=$colNum?>">
                                        <div class="drop-menu__col-title"><?=Loc::getMessage('T_CATALOG_MENU_TITLE_BRANDS')?></div>
                                        <ul class="drop-menu__list">
                                            <?php foreach($arItem['ITEMS']['BRANDS'] as $arLink): ?>
                                                <li class="drop-menu__list-item">
                                                    <a
                                                        href="<?=$arLink['LINK']?>"
                                                        class="drop-menu__list-link"
                                                        title="<?=$arLink['NAME']?>"
                                                    ><?=$arLink['NAME']?></a>
                                                </li>
                                            <?php endforeach ?>
                                            <li class="drop-menu__list-item">
                                                <a
                                                    href="<?=$arItem['LINK_BRANDS']?>"
                                                    class="drop-menu__list-link"
                                                    title="<?=$arItem['NAME']?>: <?=Loc::getMessage('T_CATALOG_MENU_LINK_BRANDS')?>"
                                                ><?=Loc::getMessage('T_CATALOG_MENU_LINK_BRANDS')?></a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif ?>
                            <?php endif?>
                            #BANNER_<?=$arItem['ID']?>#
                        </div>
                    </div>
                <?php endif ?>
            </li>
        <?php endforeach ?>
    <?php endif;?>
</ul>
<?php
$this->__component->arResult["CACHED_TPL"] = @ob_get_contents();
ob_get_clean();