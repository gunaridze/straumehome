<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $templateData
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @global CMain $APPLICATION
 */

if($arResult['ADDITIONAL_NEWS_IDS']){
    $GLOBALS['arFilterAdditionalNews'] = ['ID' => $arResult['ADDITIONAL_NEWS_IDS']];
}