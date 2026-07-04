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
<form action="<?=SITE_DIR?>search/" class="search-form header__search" method="get">
    <input
        class="search-form__input"
        placeholder="<?=Loc::getMessage('T_HEADER_SEARCH_PLACEHOLDER')?>"
        name="q"
        autocomplete="off"
    >
</form>
