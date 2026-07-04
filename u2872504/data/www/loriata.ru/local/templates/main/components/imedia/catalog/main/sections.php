<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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

\Bitrix\Iblock\Component\Tools::process404(
    '',
    ($arParams["SET_STATUS_404"] === "Y"),
    ($arParams["SET_STATUS_404"] === "Y"),
    ($arParams["SHOW_404"] === "Y"),
    $arParams["FILE_404"]
);