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

$strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
$arSectionDeleteParams = array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'));
?>
<?php if(!empty($arResult['SECTIONS'])): ?>
    <div class="brands-categories">
        <a
            href="<?=$arParams['SEF_FOLDER']?>"
            class="brands-categories__item"
            title="<?=Loc::getMessage('T_BRANDS_SECTION_LIST_ALL')?>"
        >
            <div class="brands-categories__item-img brands-categories__item-img--all">
                <img
                    src="<?=SITE_TEMPLATE_PATH?>/assets/images/icons/coat-hanger.svg"
                    alt="<?=Loc::getMessage('T_BRANDS_SECTION_LIST_ALL')?>"
                    width="50"
                    height="36"
                    loading="lazy"
                >
            </div>
            <span class="brands-categories__item-title"><?=Loc::getMessage('T_BRANDS_SECTION_LIST_ALL')?></span>
        </a>
        <?php foreach ($arResult['SECTIONS'] as $arSection):
            $this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
            $this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);
            ?>
            <a
                href="<?=$arSection['SECTION_PAGE_URL']?>"
                class="brands-categories__item"
                title="<?=$arSection['NAME']?>"
                id="<?=$this->GetEditAreaId($arSection['ID'])?>"
            >
                <span class="brands-categories__item-img">
                    <img
                            src="<?=$arSection['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                            alt="<?=$arSection['PICTURE']['ALT']?>"
                            width="<?=$arSection['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                            height="<?=$arSection['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                            srcset="<?=$arSection['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                    >
                </span>
                <span class="brands-categories__item-title"><?=$arSection['NAME']?></span>
            </a>
        <?php endforeach ?>
    </div>
<?php endif;