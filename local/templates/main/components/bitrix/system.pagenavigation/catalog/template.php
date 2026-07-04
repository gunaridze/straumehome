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

if(!$arResult["NavShowAlways"])
{
	if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
		return;
}

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");

$sideLinksCount = 3;
?>
<?php if($arResult['NavPageNomer'] > 1): ?>
    <a
            href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]-1)?>"
            class="pagination__prev"
            title="<?=Loc::getMessage('T_IMEDIA_PAGI_CATALOG_PREV')?>"
    >
        <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.292893 7.29289C-0.0976311 7.68342 -0.0976311 8.31658 0.292893 8.70711L6.65685 15.0711C7.04738 15.4616 7.68054 15.4616 8.07107 15.0711C8.46159 14.6805 8.46159 14.0474 8.07107 13.6569L2.41421 8L8.07107 2.34315C8.46159 1.95262 8.46159 1.31946 8.07107 0.928932C7.68054 0.538408 7.04738 0.538408 6.65685 0.928932L0.292893 7.29289ZM1 9H56V7H1V9Z" fill="#101112"></path>
        </svg>
    </a>
<?php endif ?>
<ol class="pagination__list">

    <?php
    $number = $arResult['NavPageNomer'] - $sideLinksCount;
    if($number < 1){
        $number = 1;
    }
    ?>
    <?php for($number; $number < $arResult['NavPageNomer']; $number++): ?>
        <li class="pagination__item">
            <a
                href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$number?>"
                title="<?=$number?>"
                class="pagination__link"
            ><?=$number?></a>
        </li>
    <?php endfor ?>

    <li class="pagination__item">
        <span class="pagination__link active"><?=$arResult['NavPageNomer']?></span>
    </li>

    <?php
    $number = $arResult['NavPageNomer'] + 1;
    $maxNumber = $arResult['NavPageNomer'] + $sideLinksCount;
    if($maxNumber > $arResult['NavPageCount']){
        $maxNumber = $arResult['NavPageCount'];
    }
    ?>
    <?php for($number; $number <= $maxNumber; $number++): ?>
        <li class="pagination__item">
            <a
                    href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$number?>"
                    title="<?=$number?>"
                    class="pagination__link"
            ><?=$number?></a>
        </li>
    <?php endfor ?>
</ol>
<?php if($arResult['NavPageNomer'] < $arResult['NavPageCount']): ?>
    <a
            href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>"
            class="pagination__next"
            title="<?=Loc::getMessage('T_IMEDIA_PAGI_CATALOG_NEXT')?>"
    >
        <svg viewBox="0 0 56 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M55.7071 8.70711C56.0976 8.31658 56.0976 7.68342 55.7071 7.29289L49.3431 0.928932C48.9526 0.538408 48.3195 0.538408 47.9289 0.928932C47.5384 1.31946 47.5384 1.95262 47.9289 2.34315L53.5858 8L47.9289 13.6569C47.5384 14.0474 47.5384 14.6805 47.9289 15.0711C48.3195 15.4616 48.9526 15.4616 49.3431 15.0711L55.7071 8.70711ZM0 9H55V7H0V9Z" fill="#101112"></path>
        </svg>
    </a>
<?php endif;