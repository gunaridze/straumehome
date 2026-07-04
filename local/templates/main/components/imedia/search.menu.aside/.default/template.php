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
    <ul class="aside-filters__list">
        <?php foreach($arResult['ITEMS'] as $arItem):

            $isOpen = $component->isSelected($arItem) || in_array($arItem['ID'], $arResult['SELECTED_SECTIONS']);

            ?>
            <li class="aside-filters__item">
                <div class="aside-filters__item-title">
                    <a
                        href="<?=$arItem['LINK']?>"
                        title="<?=$arItem['NAME']?>"
                        class="aside-filters__link<?=($component->isSelected($arItem)) ? ' active' : ''?>"
                    ><?=$arItem['NAME']?></a>
                    <?php if(!empty($arItem['ITEMS'])):?>
                        <button
                                class="aside-filters__item-drop<?=($isOpen) ? ' aside-filters__item-drop--active' : ''?>"
                                aria-label="<?=($isOpen) ? Loc::getMessage('T_SEARCH_MENU_ASIDE_TOGGLE_BACK') : Loc::getMessage('T_SEARCH_MENU_ASIDE_TOGGLE')?>"
                        >
                            <svg viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 4L7 7.6L10.75 4L12 5.2L7 10L2 5.2L3.25 4Z" fill="#101112"></path>
                            </svg>
                        </button>
                    <?php endif?>
                </div>
                <?php if(!empty($arItem['ITEMS'])):?>
                    <ul class="aside-filters__sublist"<?=($isOpen) ? ' style="display: block;"' : ''?>>
                        <?php foreach($arItem['ITEMS'] as $arChild):?>
                            <?=$component->getChildItem($arChild, $arResult['SELECTED_SECTIONS'])?>
                        <?php endforeach?>
                    </ul>
                <?php endif?>
            </li>
        <?php endforeach ?>
    </ul>
<?php endif;