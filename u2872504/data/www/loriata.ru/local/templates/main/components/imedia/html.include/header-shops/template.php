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
<a class="header__shops-search" href="<?=SITE_DIR?>shops/" title="<?=Loc::getMessage('T_HEADER_SHOPS')?>">
    <svg viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g clip-path="url(#clip0_611_26879)">
            <path d="M7.42857 1.5C10.5056 1.5 13 4.0074 13 7.10045C13 8.13484 12.7986 9.20319 12.2206 9.97321L7.42857 16.3571L2.63656 9.97321C2.11156 9.27381 1.85714 8.03738 1.85714 7.10045C1.85713 4.0074 4.35156 1.5 7.42857 1.5ZM7.42857 4.74365C6.13387 4.74365 5.08399 5.79901 5.08399 7.10044C5.08399 8.40189 6.13387 9.45724 7.42857 9.45724C8.72326 9.45724 9.77314 8.40189 9.77314 7.10045C9.77314 5.79901 8.72326 4.74365 7.42857 4.74365Z" fill="#101112"></path>
        </g>
        <defs>
            <clipPath>
                <rect width="16" height="16" fill="white" transform="matrix(-1 0 0 1 16 0.5)"></rect>
            </clipPath>
        </defs>
    </svg>
    <?=Loc::getMessage('T_HEADER_SHOPS')?>
</a>