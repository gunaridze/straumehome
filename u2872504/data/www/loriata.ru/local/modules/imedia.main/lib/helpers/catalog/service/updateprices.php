<?php
namespace Imedia\Main\Helpers\Catalog\Service;

use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\Model\Price;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\PropertyIndex\Manager;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Imedia\Main\Helpers\Catalog\Price as PriceHelper;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Debug\Logger;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class UpdatePrices
{
    public const MODULE_ID = 'imedia.main';
    public const OPTION_PREFIX = 'update_prices_';
    protected const SIZE = 1000;

    public static function process(): string
    {
        try{

            static::_process();

        } catch (\Exception $e){

            $logger = new Logger\Logger();
            $logger->routes->attach(new Logger\Route\File(
                [
                    'isEnable' => true,
                    'logDir' => str_replace('Imedia\\Main', '', static::class)
                ]
            ));

            $logger->critical($e->getMessage());

        }

        return '\\' . __METHOD__ . '();';
    }

    public static function run(): string
    {
        try{

            $types = static::getTypesQueue();
            Option::set(static::MODULE_ID, static::OPTION_PREFIX . 'last_item', 0);
            Option::set(static::MODULE_ID, static::OPTION_PREFIX . 'current_type', current($types));
            Option::set(static::MODULE_ID, static::OPTION_PREFIX . 'in_progress', 'Y');

        } catch (\Exception $e){

            $logger = new Logger\Logger();
            $logger->routes->attach(new Logger\Route\File(
                [
                    'isEnable' => true,
                    'logDir' => str_replace('Imedia\\Main', '', static::class)
                ]
            ));

            $logger->critical($e->getMessage());

        }

        return '\\' . __METHOD__ . '();';
    }

    protected static function done(): void
    {
        Option::set(static::MODULE_ID, static::OPTION_PREFIX . 'in_progress', 'N');
        \CBitrixComponent::clearComponentCache('imedia:catalog.smart.filter');
        $taggedCache = Application::getInstance()->getTaggedCache();
        $taggedCache->clearByTag('iblock_id_' . IblockHelper::getId('CATALOG'));
    }

    protected static function _process()
    {
        $inProgress = Option::get(
            static::MODULE_ID,
            static::OPTION_PREFIX . 'in_progress',
            'N'
            ) === 'Y';

        if(!$inProgress){
            return;
        }

        $currentType = (int) Option::get(static::MODULE_ID, static::OPTION_PREFIX . 'current_type', 0);
        if(!($currentType > 0)){
            static::done();
            return;
        }

        $arItems = static::getItems($currentType);
        if(empty($arItems)){
            return;
        }

        Loader::includeModule('catalog');

        $arPropertySaleValue = \CIBlockPropertyEnum::GetList(
            [],
            [
                'IBLOCK_ID' => current($arItems)['IBLOCK_ID'],
                'CODE' => Property::getCode('SALE')
            ]
        )->GetNext();
        $isSaleId = $arPropertySaleValue['ID'];

        if(in_array($currentType, [ProductTable::TYPE_SKU], true)){
            static::updateSku($arItems, $isSaleId);
        } else {
            static::updateSimple($arItems, $isSaleId);
        }

        \CBitrixComponent::clearComponentCache('bitrix:catalog.smart.filter');
    }

    public static function getTypesQueue(): array
    {
        Loader::includeModule('catalog');

        return [
            ProductTable::TYPE_PRODUCT,
            ProductTable::TYPE_OFFER,
            ProductTable::TYPE_SKU
        ];
    }

    protected static function getNextType(int $currentType): int
    {
        $types = static::getTypesQueue();
        $key = array_search($currentType, $types);
        if($key === false){
            return 0;
        }

        return (int) $types[$key + 1];
    }

    protected static function getItems(int $currentType): array
    {
        $lastItem = (int) Option::get(static::MODULE_ID, static::OPTION_PREFIX . 'last_item', 0);

        Loader::includeModule('iblock');

        $arItems = [];

        $arSort = ['ID' => 'ASC'];

        $arFilter = [
            '=IBLOCK_ID' => [
                IblockHelper::getId('CATALOG'),
                IblockHelper::getId('OFFERS')
            ],
            '=ACTIVE' => 'Y',
            '=TYPE' => $currentType,
            '>ID' => $lastItem
        ];

        $arSelect = ['ID', 'IBLOCK_ID'];

        $count = 0;
        $query = \CIBlockElement::GetList($arSort, $arFilter, false, ['nTopCount' => static::SIZE], $arSelect);
        while($row = $query->GetNext(true, false)){

            $arItems[] = $row;
            $lastItem = $row['ID'];
            $count++;

        }

        if(!empty($arItems)){
            Option::set(static::MODULE_ID, static::OPTION_PREFIX . 'last_item', $lastItem);
        }

        if(
            empty($arItems)
            || ($count < static::SIZE)
        ){
            Option::set(static::MODULE_ID, static::OPTION_PREFIX . 'last_item', 0);
            $nextType = static::getNextType($currentType);

            if($nextType > 0){
                Option::set(static::MODULE_ID, static::OPTION_PREFIX . 'current_type', $nextType);
            } else {
                static::done();
            }
        }

        return $arItems;
    }

    protected static function updateSimple(array $arItems, int $isSaleId): void
    {
        $ids = [];
        foreach($arItems as $arItem){
            $ids[] = $arItem['ID'];
        }

        $discountPriceId = PriceHelper::getId(PriceHelper::GROUP_DISCOUNT);
        $arDiscountPrices = [];
        $query = PriceTable::getList(
            [
                'select' => ['PRODUCT_ID', 'ID', 'PRICE_SCALE'],
                'filter' => [
                    '=PRODUCT_ID' => $ids,
                    '=CATALOG_GROUP_ID' => $discountPriceId
                ],
                'limit' => static::SIZE
            ]
        );
        while($row = $query->fetch()){
            $arDiscountPrices[ $row['PRODUCT_ID'] ] = $row;
        }

        $iblockCatalogId = IblockHelper::getId('CATALOG');
        $arProperties = [];
        \CIBlockElement::GetPropertyValuesArray(
            $arProperties,
            IblockHelper::getId('OFFERS'),
            ['ID' => $ids],
            ['CODE' => 'CML2_LINK'],
            [
                'PROPERTY_FIELDS' => ['ID', 'VALUE'],
                'GET_RAW_DATA' => 'Y'
            ]
        );

        $parentIds = [];
        foreach($arProperties as $arOfferProperties){
            if($arOfferProperties['CML2_LINK']['VALUE']){
                $parentIds[] = $arOfferProperties['CML2_LINK']['VALUE'];
            }
        }

        foreach($arItems as $arItem){

            $arPrice = PriceHelper::getOptimal($arItem['ID']);
            $arDiscountPrice = $arDiscountPrices[ $arItem['ID'] ];

            if((float) $arDiscountPrice['PRICE_SCALE'] === (float) $arPrice['PRICE']){
                continue;
            }

            $arFields = [
                'CATALOG_GROUP_ID' => $discountPriceId,
                'PRODUCT_ID' => $arItem['ID'],
                'CURRENCY' => $arPrice['CURRENCY'],
                'PRICE' => $arPrice['PRICE'],
                'PRICE_SCALE' => $arPrice['PRICE']
            ];

            if($arDiscountPrice['ID']){
                Price::update($arDiscountPrice['ID'], $arFields);
            } else {
                Price::add($arFields);
            }

            \CIBlockElement::SetPropertyValuesEx(
                $arItem['ID'],
                $arItem['IBLOCK_ID'],
                [
                    Property::getCode('DISCOUNT_MAX') => $arPrice['PERCENT'],
                    Property::getCode('PRICE_MIN') => $arPrice['PRICE'],
                    Property::getCode('PRICE_MAX') => $arPrice['PRICE'],
                    Property::getCode('SALE') => ($arPrice['DISCOUNT'] > 0) ? $isSaleId : null,
                ]
            );

            Manager::updateElementIndex($arItem['IBLOCK_ID'], $arItem['ID']);

        }

        if(!empty($parentIds)){
            foreach(array_unique($parentIds) as $parentId){
                Manager::updateElementIndex($iblockCatalogId, $parentId);
            }
        }
    }

    protected static function updateSku(array $arItems, int $isSaleId): void
    {
        $ids = [];
        foreach($arItems as $arItem){
            $ids[] = $arItem['ID'];
        }

        $arProperties = [];
        $arData = [];

        \CIBlockElement::GetPropertyValuesArray(
            $arProperties,
            IblockHelper::getId('OFFERS'),
            ['PROPERTY_' . Property::getCode('CML2_LINK') => $ids],
            ['CODE' => [
                Property::getCode('CML2_LINK'),
                Property::getCode('DISCOUNT_MAX'),
                Property::getCode('PRICE_MIN'),
                Property::getCode('SALE')
            ]],
            [
                'PROPERTY_FIELDS' => ['ID', 'VALUE'],
                'GET_RAW_DATA' => 'Y'
            ]
        );

        foreach($arProperties as $arOfferProperties){

            if($arOfferProperties[Property::getCode('DISCOUNT_MAX')]['VALUE'] > 0){
                $arData[ $arOfferProperties[Property::getCode('CML2_LINK')]['VALUE'] ]['DISCOUNT_MAX'][]
                    = $arOfferProperties[Property::getCode('DISCOUNT_MAX')]['VALUE'];
            }

            if($arOfferProperties[Property::getCode('PRICE_MIN')]['VALUE'] > 0){
                $arData[ $arOfferProperties[Property::getCode('CML2_LINK')]['VALUE'] ]['PRICE'][]
                    = $arOfferProperties[Property::getCode('PRICE_MIN')]['VALUE'];
            }

            if($arOfferProperties[Property::getCode('SALE')]['VALUE']){
                $arData[ $arOfferProperties[Property::getCode('CML2_LINK')]['VALUE'] ]['SALE'] = true;
            }

        }

        foreach($arItems as $arItem){

            $arItemData = $arData[$arItem['ID']];

            \CIBlockElement::SetPropertyValuesEx(
                $arItem['ID'],
                $arItem['IBLOCK_ID'],
                [
                    Property::getCode('DISCOUNT_MAX') => (int) max((array) $arItemData['DISCOUNT_MAX']),
                    Property::getCode('PRICE_MIN') => (float) min((array) $arItemData['PRICE']),
                    Property::getCode('PRICE_MAX') => (float) max((array) $arItemData['PRICE']),
                    Property::getCode('SALE') => ($arItemData['SALE']) ? $isSaleId : null,
                ]
            );

            Manager::updateElementIndex($arItem['IBLOCK_ID'], $arItem['ID']);

        }

    }
}