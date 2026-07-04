<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

$this->setFrameMode(true);

$half = ceil($arParams['PAGE_ELEMENT_COUNT'] / 2);
?>
<?php ob_start() ?>
<?php if(!empty($arResult['ITEMS'])): ?>
    <catalog-section
        :items='<?=Json::encode($arResult['ITEMS'])?>'
        :pagination='<?=Json::encode($arResult['PAGINATION'])?>'
        :sort-list='<?=Json::encode($arParams['SORT_LIST'])?>'
        sort-selected="<?=$arParams['SORT_SELECTED']?>"
        half="<?=$half?>"
    >
        <div class="catalog__cards">

            <?php for($i = 0; $i <= $half; $i++):
                if(!isset($arResult['ITEMS'][$i])){
                    continue;
                }
                ?>
                <?php $APPLICATION->IncludeComponent(
                    'imedia:catalog.item',
                    '',
                    [
                        'ITEM' => $arResult['ITEMS'][$i]
                    ],
                    $this->component,
                    ['HIDE_ICONS' => true]
                ) ?>
            <?php endfor ?>

            #BANNER_CATALOG_SECTION#

            <?php for($i = ($half + 1); $i <= $arParams['PAGE_ELEMENT_COUNT']; $i++):
                if(!isset($arResult['ITEMS'][$i])){
                    continue;
                }
                ?>
                <?php $APPLICATION->IncludeComponent(
                'imedia:catalog.item',
                '',
                [
                    'ITEM' => $arResult['ITEMS'][$i]
                ],
                $this->component,
                ['HIDE_ICONS' => true]
            ) ?>
            <?php endfor ?>


        </div>
        <template slot="banner">#BANNER_CATALOG_SECTION#</template>
    </catalog-section>
<?php endif ?>
<?php if (
    ($arParams['HIDE_SECTION_DESCRIPTION'] !== 'Y')
    && $arResult['DESCRIPTION']
):?>
    <div class="seo-text catalog-page__seo-text"><?=$arResult['DESCRIPTION']?></div>
<?php endif;?>
<?php
$this->__component->arResult["CACHED_TPL"] = @ob_get_contents();
ob_get_clean();
