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

$APPLICATION->SetTitle(Loc::getMessage('T_PAGE_404_PAGE_TITLE'));
$APPLICATION->SetPageProperty('title-type', 'hidden');
$APPLICATION->SetPageProperty('classes--page', 'not-found-page');
$APPLICATION->SetPageProperty('title', Loc::getMessage('T_PAGE_404_PAGE_TITLE'));
$APPLICATION->SetPageProperty('description', '');
?>
<h1 class="not-found-page__title"><?=Loc::getMessage('T_PAGE_404_TITLE')?></h1>
<div class="not-found-page__subtitle"><?=Loc::getMessage('T_PAGE_404_DESCRIPTION')?></div>