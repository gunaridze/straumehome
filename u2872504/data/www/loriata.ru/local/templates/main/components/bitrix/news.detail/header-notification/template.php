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
<?php if(!empty($arResult['PROPERTIES']['NOTIFICATION']['VALUE'])):

    $speed = (int) $arResult['PROPERTIES']['NOTIFICATION_SPEED']['VALUE'];
    if(!($speed > 0)){
        $speed = 15;
    }

    $repeat = (int) $arResult['PROPERTIES']['NOTIFICATION_REPEAT']['VALUE'];
    if($repeat < 1){
        $repeat = 1;
    }

    ?>
    <div class="header-info-line marquee__parent">
        <div class="header-info-line__marquee marquee__wrapper">
            <div class="marquee">
                <?php for($i = 0; $i < $repeat; $i++):?>
                    <div class="marquee__content" style="animation-duration: <?=$speed?>s;">
                        <?php foreach($arResult['PROPERTIES']['NOTIFICATION']['VALUE'] as $message): ?>
                            <span><?=$message?></span>
                        <?php endforeach ?>
                    </div>
                <?php endfor?>
            </div>
        </div>
        <button class="header-info-line__close" type="button" aria-label="<?=Loc::getMessage('T_HEADER_NOTIFICATION_CLOSE')?>">
            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.9929 3L8 6.99286L4.00714 3L3 4.00714L6.99286 8L3 11.9929L4.00714 13L8 9.00714L11.9929 13L13 11.9929L9.00714 8L13 4.00714L11.9929 3Z" fill="#C0C0C0" />
            </svg>
        </button>
    </div>
<?php endif;