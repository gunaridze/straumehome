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
<?php if(
    $arResult['PROPERTIES']['PHONE']['VALUE']
    || $arResult['PROPERTIES']['WHATSAPP']['VALUE']
): ?>
    <div class="header__contact">
        <div class="header__call">
            <?php if($arResult['PROPERTIES']['PHONE']['VALUE']):
                $phone = preg_replace('/[^\d+]/', '', $arResult['PROPERTIES']['PHONE']['VALUE']);
                ?>
                <a
                    class="header__call-link"
                    href="tel:<?=$phone?>"
                    title="<?=$arResult['PROPERTIES']['PHONE']['VALUE']?>"
                    target="_blank"
                    rel="nofollow"
                >
                    <svg viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.1638 11.24L11.2838 10.12C11.4347 9.97104 11.6256 9.86906 11.8332 9.82646C12.0409 9.78385 12.2565 9.80246 12.4538 9.88002L13.8188 10.425C14.0183 10.506 14.1892 10.6441 14.3102 10.8221C14.4313 11 14.4969 11.2098 14.4988 11.425V13.925C14.4977 14.0714 14.4669 14.2161 14.4084 14.3502C14.3498 14.4844 14.2648 14.6054 14.1583 14.7058C14.0518 14.8062 13.926 14.8841 13.7886 14.9346C13.6512 14.9852 13.5051 15.0074 13.3588 15C3.79385 14.405 1.86385 6.30502 1.49885 3.20502C1.4819 3.05279 1.49738 2.89869 1.54427 2.75288C1.59116 2.60706 1.66839 2.47282 1.77088 2.35899C1.87337 2.24516 1.9988 2.15432 2.13893 2.09245C2.27905 2.03058 2.43068 1.99908 2.58385 2.00002H4.99885C5.21435 2.00066 5.42474 2.06575 5.60294 2.18694C5.78115 2.30812 5.91903 2.47984 5.99885 2.68002L6.54385 4.04502C6.62397 4.24155 6.64442 4.45734 6.60262 4.66543C6.56083 4.87351 6.45865 5.06467 6.30885 5.21502L5.18885 6.33502C5.18885 6.33502 5.83385 10.7 10.1638 11.24Z" fill="#101112"></path>
                    </svg>
                    <?=$arResult['PROPERTIES']['PHONE']['VALUE']?>
                </a>
                <span class="header__call-descr"><?=Loc::getMessage('T_HEADER_CONTACT_PHONE_DESCRIPTION')?></span>
            <?php endif ?>
        </div>
        <?php if($arResult['PROPERTIES']['WHATSAPP']['VALUE']): ?>
            <a
                href="<?=$arResult['PROPERTIES']['WHATSAPP']['VALUE']?>"
                class="header__whatsup"
                target="_blank"
                rel="nofollow"
                title="<?=Loc::getMessage('T_HEADER_CONTACT_WHATSAPP')?>"
            >
                <img class="header__whatsup-icon" src="<?=SITE_TEMPLATE_PATH?>/assets/images/icons/whatsup.svg" alt="whatsup" width="16" height="16">
                <?=Loc::getMessage('T_HEADER_CONTACT_WHATSAPP')?>
            </a>
        <?php endif ?>
    </div>
<?php endif;