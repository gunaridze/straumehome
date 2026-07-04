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
<?php if(!empty($arResult['ITEMS'])):?>
    <div class="brands-alphabet">
        <?php foreach($arResult['ITEMS'] as $arItem):?>
            <div class="brands-alphabet__item">
                <dt>
                    <?php
                    if($arItem['LETTER'] === 'OTHER') {
                        echo Loc::getMessage('T_BRANDS_LIST_LETTER_OTHER');
                    } elseif ($arItem['LETTER'] === 'CYRILLIC') {
                        echo Loc::getMessage('T_BRANDS_LIST_LETTER_CYRILLIC');
                    } else {
                        echo $arItem['LETTER'];
                    }
                    ?>
                </dt>
                <dd>
                    <div class="brands-alphabet__row">
                        <?php foreach($arItem['COLS'] as $arCol): ?>
                            <div class="brands-alphabet__col">
                                <?php foreach($arCol as $arItem): ?>
                                    <a
                                        href="<?=$arItem['DETAIL_PAGE_URL']?>"
                                        title="<?=$arItem['NAME']?>"
                                        class="brands-alphabet__link"
                                    >
                                        <?=$arItem['NAME']?>
                                    </a>
                                <?php endforeach ?>
                            </div>
                        <?php endforeach ?>
                    </div>
                </dd>
            </div>
        <?php endforeach ?>
    </div>
<?php endif;