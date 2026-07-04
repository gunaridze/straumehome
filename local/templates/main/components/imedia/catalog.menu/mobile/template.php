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
<ul class="burger-menu__list">
    <?php if(!empty($arResult['ITEMS'])): ?>
        <?php foreach($arResult['ITEMS'] as $arItem): ?>
            <li class="burger-menu__list-item">
                <?php if(empty($arItem['ITEMS'])):?>
                    <a
                        href="<?=$arItem['LINK']?>"
                        title="<?=$arItem['NAME']?>"
                        class="burger-menu__list-link<?=($arItem['IS_PRIMARY']) ? ' burger-menu__list-link--red' : ''?>"
                    ><?=$arItem['NAME']?></a>
                <?php else: ?>
                    <span><?=$arItem['NAME']?></span>
                    <ul class="burger-menu__sublist">
                        <?php if(!empty($arItem['ITEMS']['SECTIONS'])):?>
                            <?php foreach($arItem['ITEMS']['SECTIONS'] as $arSection):?>
                                <li class="burger-menu__list-item">
                                    <span><?=$arSection['NAME']?></span>
                                    <ul class="burger-menu__sublist">
                                        <?php foreach($arSection['ITEMS'] as $arLink):?>
                                            <li class="burger-menu__list-item">
                                                <a
                                                    href="<?=$arLink['LINK']?>"
                                                    class="burger-menu__list-link"
                                                    title="<?=$arLink['NAME']?>"
                                                ><?=$arLink['NAME']?></a>
                                            </li>
                                        <?php endforeach?>
                                        <li class="burger-menu__list-item">
                                            <a
                                                href="<?=$arSection['LINK']?>"
                                                class="burger-menu__list-link"
                                                title="<?=$arSection['NAME']?>"
                                            ><?=Loc::getMessage('T_CATALOG_MENU_MOBILE_LINK_ALL')?></a>
                                        </li>
                                    </ul>
                                </li>
                            <?php endforeach?>
                        <?php else:?>
                            <?php if(!empty($arItem['ITEMS']['MAIN'])): ?>
                                <li class="burger-menu__list-item">
                                    <span><?=$arItem['TITLE_PLURAL']?></span>
                                    <ul class="burger-menu__sublist">
                                        <?php foreach ($arItem['ITEMS']['MAIN'] as $arLink): ?>
                                            <li class="burger-menu__list-item">
                                                <a
                                                    class="burger-menu__list-link"
                                                    href="<?=$arLink['LINK']?>"
                                                    title="<?=$arLink['NAME']?>"
                                                ><?=$arLink['NAME']?></a>
                                            </li>
                                        <?php endforeach ?>
                                        <li class="burger-menu__list-item">
                                            <a
                                                class="burger-menu__list-link"
                                                href="<?=$arItem['LINK']?>"
                                                title="<?=$arItem['NAME']?>"
                                            ><?=Loc::getMessage('T_CATALOG_MENU_MOBILE_LINK_ALL')?></a>
                                        </li>
                                    </ul>
                                </li>
                            <?php endif ?>
                            <?php if(!empty($arItem['ITEMS']['POPULAR'])): ?>
                                <li class="burger-menu__list-item">
                                    <span><?=Loc::getMessage('T_CATALOG_MENU_MOBILE_TITLE_POPULAR')?></span>
                                    <ul class="burger-menu__sublist">
                                        <?php foreach ($arItem['ITEMS']['POPULAR'] as $arLink): ?>
                                            <li class="burger-menu__list-item">
                                                <a
                                                    class="burger-menu__list-link"
                                                    href="<?=$arLink['LINK']?>"
                                                    title="<?=$arLink['NAME']?>"
                                                ><?=$arLink['NAME']?></a>
                                            </li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php endif ?>
                            <?php if(!empty($arItem['ITEMS']['BRANDS'])): ?>
                                <li class="burger-menu__list-item">
                                    <span><?=Loc::getMessage('T_CATALOG_MENU_MOBILE_TITLE_BRANDS')?></span>
                                    <ul class="burger-menu__sublist">
                                        <?php foreach ($arItem['ITEMS']['MAIN'] as $arLink): ?>
                                            <li class="burger-menu__list-item">
                                                <a
                                                    class="burger-menu__list-link"
                                                    href="<?=$arLink['LINK']?>"
                                                    title="<?=$arLink['NAME']?>"
                                                ><?=$arLink['NAME']?></a>
                                            </li>
                                        <?php endforeach ?>
                                        <li class="burger-menu__list-item">
                                            <a
                                                class="burger-menu__list-link"
                                                href="<?=$arItem['LINK_BRANDS']?>"
                                                title="<?=$arItem['NAME']?>: <?=Loc::getMessage('T_CATALOG_MENU_MOBILE_LINK_BRANDS')?>"
                                            ><?=Loc::getMessage('T_CATALOG_MENU_MOBILE_LINK_BRANDS')?></a>
                                        </li>
                                    </ul>
                                </li>
                            <?php endif ?>
                        <?php endif?>
                    </ul>
                <?php endif?>
            </li>
        <?php endforeach ?>
    <?php endif;?>
</ul>