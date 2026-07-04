<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $templateData
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @global CMain $APPLICATION
 */

if($arResult['ARTICLES']){
    $GLOBALS['arFilterOtherArticles'] = ['ID' => $arResult['ARTICLES']];
}