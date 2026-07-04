<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $templateData
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @global CMain $APPLICATION
 */

use Imedia\Main\Helpers\Page\Meta;
use Imedia\Main\Helpers\Image\Share;

global $APPLICATION;

$result = $arResult['CACHED_TPL'];

$codes = ['DELIVERY', 'PAYMENT'];

foreach ($codes as $arCode) {
    $result = preg_replace_callback(
        "/#$arCode#/",
        function ($matches) use ($arCode, $APPLICATION) {
            ob_start();

            $APPLICATION->IncludeComponent('bitrix:main.include', '',
                [
                    'AREA_FILE_SHOW' => 'file',
                    'PATH' => SITE_DIR . 'local/include/' . SITE_ID . '/product_' . mb_strtolower($arCode) . '.php',
                ]
            );

            return @ob_get_clean();
        },
        $result
    );
}

echo $result;

if ($arResult['RECOMMEND_PRODUCTS_FILTER']) {
    $GLOBALS['arFilterRecommendProducts'] = $arResult['RECOMMEND_PRODUCTS_FILTER'];
}

if ($arResult['SIMILAR_PRODUCTS_FILTER']) {
    $GLOBALS['arFilterSimilarProducts'] = $arResult['SIMILAR_PRODUCTS_FILTER'];
}

if($arResult['DETAIL_PICTURE']['SRC']){

    $shareImage = Share::create($arResult['DETAIL_PICTURE']['SRC']);
    if($shareImage){
        Meta::add(['og:image' => $shareImage]);
    }

}