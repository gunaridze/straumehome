<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {die();}

use Bitrix\Sale\PropertyValueCollection;
use Bitrix\Iblock\ElementTable;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Image\Resize;

$elementIds = [];
$orderIds = [];

foreach ($arResult['ORDERS'] as $arOrder) {
    $elementIds = array_merge($elementIds, array_column($arOrder['BASKET_ITEMS'], "PRODUCT_ID"));
    $orderIds[] = $arOrder['ORDER']['ID'];
}
$elementIds = array_unique($elementIds);

$query = ElementTable::getList(
    [
        'select' => ['ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
        'filter' => [
            '=ID' => $elementIds,
            '=IBLOCK_ID' => [
                IblockHelper::getId('CATALOG'),
                IblockHelper::getId('OFFERS')
            ]
        ],
    ]
);
while ($row = $query->fetch()){

    if(!$row['PREVIEW_PICTURE']){
        $row['PREVIEW_PICTURE'] = $row['DETAIL_PICTURE'];
    }

    if(!$row['PREVIEW_PICTURE']){
        continue;
    }

    $dimensions = [
        'width' => 75,
        'height' => 94
    ];

    $sizes = [
        'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
        'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
    ];

    $arResult['PICTURES'][$row['ID']] = Resize::setSelfResizeArray(
        $row['PREVIEW_PICTURE'],
        $sizes
    );
}

$query = PropertyValueCollection::getList(
    [
        'select' => ['CODE', 'ORDER_ID', 'VALUE'],
        'filter' => [
            '=CODE' => ['DELIVERY_DATE', 'TRACKING_LINK'],
            '=ORDER_ID' => $orderIds,
        ]
    ]
);

while ($row = $query->fetch()) {
    $arResult['ORDER_PROPERTIES'][$row['ORDER_ID']][$row['CODE']] = $row['VALUE'];
}