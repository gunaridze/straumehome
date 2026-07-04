<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Catalog\Price as PriceHelper;
use Imedia\Main\Helpers\Sale\Cart;

$mobileColumns = isset($this->arParams['COLUMNS_LIST_MOBILE'])
    ? $this->arParams['COLUMNS_LIST_MOBILE']
    : $this->arParams['COLUMNS_LIST'];
$mobileColumns = array_fill_keys($mobileColumns, true);

$offerProperties = Cart::getOfferProperties();

$priceTypeBase = PriceHelper::getId(PriceHelper::GROUP_BASE);

$productIdsForBasePrices = [];
foreach($this->basketItems as $row){
    if((int) $row['PRICE_TYPE_ID'] !== $priceTypeBase){
        $productIdsForBasePrices[] = $row['PRODUCT_ID'];
    }
}

$arBasePrices = [];
if(!empty($productIdsForBasePrices)){

    $query = PriceTable::getList(
        [
            'select' => ['PRICE_SCALE', 'PRODUCT_ID'],
            'filter' => [
                '=PRODUCT_ID' => $productIdsForBasePrices,
                '=CATALOG_GROUP_ID' => $priceTypeBase
            ],
            'limit' => count($productIdsForBasePrices)
        ]
    );
    while($row = $query->fetch()){
        $arBasePrices[ $row['PRODUCT_ID'] ] = round($row['PRICE_SCALE'], 2);
    }

}

$result['items'] = [];
foreach ($this->basketItems as $row){

    $basePrice = $arBasePrices[ $row['PRODUCT_ID'] ];
    if($basePrice && ($basePrice > $row['BASE_PRICE'])){
        $row['BASE_PRICE'] = $basePrice;
    }

    $discount = $row['BASE_PRICE'] - $row['PRICE'];
    $discountPercent = 0;
    if($discount > 0){
        $discountPercent = round(
            $discount / ($row['BASE_PRICE'] * 0.01)
        );
    }

    $rowData = [
        'id' => $row['ID'],
        'productId' => $row['PRODUCT_ID'],
        'parentId' => $row['PARENT_ID'],
        'name' => $row['~NAME'] ?? $row['NAME'],
        'quantity' => $row['QUANTITY'],
        'hash' => $row['HASH'],
        'sort' => $row['SORT'],
        'url' => $row['DETAIL_PAGE_URL'],
        'currency' => $row['CURRENCY'],
        'price' => [
            'base' => $row['BASE_PRICE'],
            'result' => $row['PRICE'],
            'discount' => $discount,
            'discountPercent' => $discountPercent
        ],
        'measureRatio' => $row['MEASURE_RATIO'] ?? 1,
        'measureText' => $row['MEASURE_TEXT'],
        'availableQuantity' => ($row['AVAILABLE_QUANTITY']) ?: 0,
        'checkMaxQuantity' => $row['CHECK_MAX_QUANTITY'],
        'module' => $row['MODULE'],
        'productProviderClass' => $row['PRODUCT_PROVIDER_CLASS'],
        'notAvailable' => $row['NOT_AVAILABLE'] === true,
        'delayed' => $row['DELAY'] === 'Y',
        'canBuy' => $row['CAN_BUY'] === 'Y',
        'skuList' => [],
        'COLUMN_LIST' => [],
        'showLabels' => false,
        'labels' => [],
        'picture' => $row['PICTURE'],
        'properties' => []
    ];

    foreach($row['PROPS'] as $property){
        if(
            !in_array($property['CODE'], $offerProperties, true)
            || !$property['VALUE']
        ){
            continue;
        }

        $rowData['properties'][] = [
            'code' => $property['CODE'],
            'name' => $property['NAME'],
            'value' => $property['VALUE']
        ];
    }

    $result['items'][] = $rowData;
}

$totalData = [
    'DISABLE_CHECKOUT' => (int) $result['ORDERABLE_BASKET_ITEMS_COUNT'] === 0,
    'PRICE' => $result['allSum'],
    'PRICE_FORMATED' => $result['allSum_FORMATED'],
    'PRICE_WITHOUT_DISCOUNT_FORMATED' => $result['PRICE_WITHOUT_DISCOUNT'],
    'CURRENCY' => $result['CURRENCY']
];

if ($result['DISCOUNT_PRICE_ALL'] > 0){
    $totalData['DISCOUNT_PRICE_FORMATED'] = $result['DISCOUNT_PRICE_FORMATED'];
}

if ($this->priceVatShowValue === 'Y'){
    $totalData['SHOW_VAT'] = true;
    $totalData['VAT_SUM_FORMATED'] = $result['allVATSum_FORMATED'];
    $totalData['SUM_WITHOUT_VAT_FORMATED'] = $result['allSum_wVAT_FORMATED'];
}

if (
    $this->hideCoupon !== 'Y' &&
    !empty($result['COUPON_LIST'])
){
    $totalData['COUPON_LIST'] = $result['COUPON_LIST'];

    foreach ($totalData['COUPON_LIST'] as &$coupon){
        if ($coupon['JS_STATUS'] === 'ENTERED'){
            $coupon['CLASS'] = 'danger';
        } elseif ($coupon['JS_STATUS'] === 'APPLYED'){
            $coupon['CLASS'] = 'muted';
        } else {
            $coupon['CLASS'] = 'danger';
        }
    }
}

$totalData['DISCOUNT_PRICE'] = $result['DISCOUNT_PRICE_ALL'];

$result['total'] = $totalData;