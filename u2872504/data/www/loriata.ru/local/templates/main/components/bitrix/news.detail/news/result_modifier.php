<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Imedia\Main\Helpers\Image\Resize;

$dimensions = [
    'width' => 724,
    'height' => 350
];

$sizes = [
    'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
    'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
];

$arResult['DETAIL_PICTURE'] = Resize::setSelfResizeArray(
    $arResult['DETAIL_PICTURE'],
    $sizes
);

$arResult['ADDITIONAL_NEWS_IDS'] = $arResult['PROPERTIES']['ADDITIONAL_NEWS']['VALUE'];

$this->__component->SetResultCacheKeys(['ADDITIONAL_NEWS_IDS']);
