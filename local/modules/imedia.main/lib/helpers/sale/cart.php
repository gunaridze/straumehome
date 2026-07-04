<?php
namespace Imedia\Main\Helpers\Sale;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Sale\Fuser;
use Bitrix\Main\Context;
use Bitrix\Sale\Registry;
use Bitrix\Iblock\ElementTable;
use Bitrix\Catalog\Product\PropertyCatalogFeature;
use Bitrix\Catalog\ProductTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Iblock\Iblock;

class Cart
{
    public const ERROR_CODE_FEWER_AVAILABLE = 301;

    public static function add(int $productId, int $quantity = 1, $basket = null, bool $save = true): Result
    {
        $result = new Result();

        Loader::includeModule('sale');
        Loader::includeModule('catalog');
        Loader::includeModule('iblock');

        $productProperties = [];

        $arFilter = [
            '=IBLOCK_ID' => [
                Iblock::getId('CATALOG'),
                Iblock::getId('OFFERS')
            ],
            '=ID' => $productId
        ];

        $arSelect = [
            'XML_ID',
            'IBLOCK_XML_ID'
        ];

        foreach(Cart::getOfferProperties() as $code){
            $arSelect[] = 'PROPERTY_' . Property::getCode($code);
        }

        $arProductFields = \CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect)
            ->GetNext(true, false);

        $productXmlId = $arProductFields['XML_ID'];
        $mxResult = \CCatalogSku::GetProductInfo($productId);
        if(isset($mxResult['ID'])){
            $arParentFields = ElementTable::getList(
                [
                    'select' => ['XML_ID'],
                    'filter' => ['=ID' => $mxResult['ID']],
                    'limit' => 1
                ]
            )->fetch();

            if(strpos($productXmlId, '#') === false){
                $productXmlId = $arParentFields['XML_ID'] . '#' . $productXmlId;
            }
        }

        $propertyIds = PropertyCatalogFeature::getBasketPropertyCodes($mxResult['OFFER_IBLOCK_ID']);
        if(!empty($propertyIds)){
            \CIBlockElement::GetPropertyValuesArray(
                $arProperties,
                $mxResult['OFFER_IBLOCK_ID'],
                ['ID' => $productId],
                ['ID' => $propertyIds]
            );

            $sortIndex = 0;
            foreach($arProperties[$productId] as $arProperty){

                if(!$arProperty['VALUE']){
                    continue;
                }

                $sortIndex++;
                $productProperties[] = [
                    'CODE' => $arProperty['CODE'],
                    'VALUE' => $arProperty['VALUE'],
                    'SORT' => $sortIndex,
                    'NAME' => $arProperty['NAME']
                ];
            }
        }

        $productProperties[] = [
            'CODE' => 'CATALOG.XML_ID',
            'VALUE' => $arProductFields['IBLOCK_XML_ID'],
            'SORT' => 100,
            'NAME' => 'Catalog XML_ID'
        ];

        $productProperties[] = [
            'CODE' => 'PRODUCT.XML_ID',
            'VALUE' => $productXmlId,
            'SORT' => 100,
            'NAME' => 'Product XML_ID'
        ];

        foreach(Cart::getOfferProperties() as $code){
            $arSelect[] = 'PROPERTY_' . $code;
        }

        foreach(Cart::getOfferProperties() as $code){

            if(!$arProductFields['PROPERTY_' . Property::getCode($code) . '_VALUE']){
                continue;
            }

            $productProperties[] = [
                'CODE' => $code,
                'VALUE' => $arProductFields['PROPERTY_' . Property::getCode($code) . '_VALUE'],
                'SORT' => 500,
                'NAME' => Loc::getMessage('CART_PROPERTY_' . $code)
            ];

        }

        if(!$basket){
            $basket = static::getBasket();
        }

        $productFields = ProductTable::getList(
            [
                'select' => [
                    'ID', 'TYPE', 'AVAILABLE', 'CAN_BUY_ZERO', 'QUANTITY_TRACE', 'QUANTITY'
                ],
                'filter' => ['=ID' => $productId],
                'limit' => 1
            ]
        )->fetch();

        if($productFields['AVAILABLE'] === 'N'){
            $result->addError(new Error(Loc::getMessage('CART_ERROR_NOT_AVAILABLE')));
        } else {

            $productQuantityInCart = 0;
            foreach($basket as $item){
                if($productId === (int) $item->getProductId()){
                    $productQuantityInCart += $item->getQuantity();
                }
            }

            $remain = $productFields['QUANTITY'] - $productQuantityInCart;

            $skipAdd = false;

            if($remain < $quantity){
                $quantity = $remain;

                if($quantity > 0){
                    $result->addError(new Error(
                        Loc::getMessage('CART_ERROR_FEWER_AVAILABLE', ['#QUANTITY#' => $quantity]),
                        static::ERROR_CODE_FEWER_AVAILABLE
                    ));
                } else {
                    $result->addError(new Error(Loc::getMessage('CART_ERROR_FEWER_NOT_AVAILABLE')));
                    $skipAdd = true;
                }
            }

            if(!$skipAdd){
                $isNew = true;

                if ($item = $basket->getExistsItem('catalog', $productId, $productProperties)) {
                    $item->setField('QUANTITY', $item->getQuantity() + $quantity);
                    $isNew = false;
                }

                if($isNew){
                    Loader::includeModule('iblock');

                    $arFilter = [
                        'ID' => $productId,
                        'IBLOCK_ID' => [
                            Iblock::getId('CATALOG'),
                            Iblock::getId('OFFERS')
                        ],
                        'IBLOCK_TYPE' => 'catalog'
                    ];

                    $arSelect = [
                        'ID',
                        'IBLOCK_ID',
                        'DETAIL_PAGE_URL',
                        'XML_ID'
                    ];

                    $arElement = \CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect)
                        ->GetNext(true, false);

                    $item = $basket->createItem('catalog', $productId);

                    $currency = CurrencyManager::getBaseCurrency();

                    $itemFields = [
                        'QUANTITY' => $quantity,
                        'CURRENCY' => $currency,
                        'LID' => $basket->getSiteId(),
                        'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
                        'DETAIL_PAGE_URL' => $arElement['DETAIL_PAGE_URL'],
                        'PRODUCT_XML_ID' => $arElement['XML_ID'],
                        'CATALOG_XML_ID' => $arProductFields['IBLOCK_XML_ID']
                    ];

                    $item->setFields($itemFields);

                    $basketPropertyCollection = $item->getPropertyCollection();
                    $basketPropertyCollection->setProperty($productProperties);
                }
            }

            if($save){
                $saveResult = $basket->save();
                if(!($saveResult->isSuccess())){
                    return $saveResult;
                }
            }

        }

        return $result;
    }

    public static function refresh(): array
    {
        $basket = static::getBasket();

        $items = [];

        foreach($basket as $item){
            $productId = (int) $item->getProductId();

            $arItem = [
                'id' => (int) $item->getId(),
                'productId' => $productId,
                'delayed' => $item->isDelay(),
                'quantity' => (int) $item->getQuantity(),
                'name' => $item->getField('NAME'),
                'price' => [
                    'currency' => $item->getCurrency(),
                    'base' => [
                        'perItem' => [
                            'raw' => (float) $item->getBasePrice()
                        ],
                        'total' => [
                            'raw' => (float) $item->getBasePrice() * (int) $item->getQuantity()
                        ]
                    ],
                    'result' => [
                        'perItem' => [
                            'raw' => (float) $item->getPrice()
                        ],
                        'total' => [
                            'raw' => (float) $item->getFinalPrice()
                        ]
                    ]
                ]
            ];

            $items[] = $arItem;
        }

        return $items;
    }

    /**
     * @param int|null $fUserId
     * @param string|null $siteId
     * @return void
     */
    public static function clear(int $fUserId = null, string $siteId = null): void
    {
        $basket = static::getBasket($fUserId, $siteId);
        $isEmpty = static::isEmpty($basket);

        if(!$isEmpty){
            foreach($basket as $item){
                $item->delete();
            }

            $basket->save();
        }
    }

    /**
     * @param $basket
     * @return bool
     */
    public static function isEmpty($basket = null): bool
    {
        if(!$basket){
            $basket = static::getBasket();
        }

        foreach($basket as $item){
            return false;
        }

        return true;
    }

    /**
     * @param int|null $fUserId
     * @param string|null $siteId
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getBasket(int $fUserId = null, string $siteId = null)
    {
        Loader::includeModule('sale');

        if(!$fUserId){
            $fUserId = Fuser::getId();
        }

        if(!$siteId){
            $siteId = Context::getCurrent()->getSite();
        }

        $registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
        $basketClass = $registry->getBasketClassName();

        return $basketClass::loadItemsForFUser($fUserId, $siteId);
    }

    public static function remove(int $id, $basket = null): void
    {
        $needSave = false;

        if(!$basket){
            $basket = static::getBasket();
        }

        foreach($basket as $item){
            if((int) $item->getId() === $id){
                $item->delete();
                $needSave = true;
                break;
            }
        }

        if($needSave){
            $basket->save();
        }
    }

    public static function getOfferProperties(): array
    {
        return [
            'SKU',
            'COLOR',
            'SIZE'
        ];
    }
}