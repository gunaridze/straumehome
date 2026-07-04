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

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);
?>
<?php if(!empty($arResult['ITEMS'])): ?>
    <div class="footer__cols">
        <?php foreach($arResult['ITEMS'] as $arSections): ?>
            <div class="footer__col">
                <?php foreach($arSections as $arSection): ?>
                    <div class="footer__col-title">
                        <?php if($arSection['UF_LINK']): ?>
                            <a
                                href="<?=$arSection['UF_LINK']?>"
                                title="<?=$arSection['NAME']?>"
                            ><?=$arSection['NAME']?></a>
                        <?php else: ?>
                            <?=$arSection['NAME']?>
                        <?php endif ?>
                    </div>
                    <?php if(!empty($arSection['ITEMS'])): ?>
                        <ul class="footer__list">
                            <?php foreach($arSection['ITEMS'] as $arItem): ?>
                                <li class="footer__list-item">
                                    <a
                                        href="<?=$arItem['PROPERTIES']['LINK']['VALUE']?>"
                                        title="<?=$arItem['NAME']?>"
                                    ><?=$arItem['NAME']?></a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
        <?php endforeach ?>
    </div>
<?php endif;