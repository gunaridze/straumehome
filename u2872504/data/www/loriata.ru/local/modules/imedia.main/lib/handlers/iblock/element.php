<?php
namespace Imedia\Main\Handlers\Iblock;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\PropertyIndex\Manager;
use Bitrix\Main\Loader;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\PropertyTable;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Iblock\Brand;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Models\DefferedProduct\DefferedProductTable;
use Imedia\Main\Helpers\Catalog\Service\Deactivate;

class Element
{
    public array $arFields = [];
    private static self $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function getInstance(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function onBeforeIBlockElementAdd(array &$arFields)
    {
        if($arFields['BREAK'] === 'Y'){
            return;
        }

        $iblockCode = IblockHelper::getCode((int) $arFields['IBLOCK_ID']);
        switch($iblockCode) {
            case 'INFO':

                $iblock = Iblock::wakeUp($arFields['IBLOCK_ID']);
                $entity = $iblock->getEntityDataClass();

                $query = $entity::getList(
                    [
                        'select' => ['ID'],
                        'limit' => 1
                    ]
                );
                if($query->fetch()){
                    global $APPLICATION;
                    $APPLICATION->throwException(Loc::getMessage('IMEDIA_MAIN_HANDLERS_IBLOCK_ELEMENT_ERROR_ADD_INFO'));
                    return false;
                }

                break;
            default:
                break;
        }
    }

    public static function onBeforeIBlockElementUpdate(array &$arFields)
    {
        if($arFields['BREAK'] === 'Y'){
            return;
        }

        $iblockCode = IblockHelper::getCode((int) $arFields['IBLOCK_ID']);
        switch($iblockCode) {
            case 'CATALOG':

                static::removeEmptyPropertiesFromImport($arFields);

                break;
            default:
                break;
        }

    }

    public static function onAfterIBlockElementAdd(array $arFields)
    {
        if(
            !((int) $arFields['ID'] > 0)
            && ($arFields['BREAK'] === 'Y')
        ){
            return;
        }

        static::onAddnUpdate($arFields);
    }

    public static function onAfterIBlockElementUpdate(array $arFields)
    {
        if(
            !((int) $arFields['ID'] > 0)
            && ($arFields['BREAK'] === 'Y')
        ){
            return;
        }

        static::checkNeedRemoveFromDeffered($arFields);
        static::onAddnUpdate($arFields);
    }

    public static function onBeforeIBlockElementDelete(int $id)
    {
        $handler = self::getInstance();

        $arFilter = [
            '=ID' => $id
        ];

        $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_' . Property::getCode('CML2_LINK')];

        $query = \CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
        if($row = $query->GetNext(true, false)){
            $handler->arFields = $row;
        }

        static::setIblockCode($handler->arFields);
        switch($handler->arFields['IBLOCK_CODE']) {
            case 'INFO':
                global $APPLICATION;
                $APPLICATION->throwException(Loc::getMessage('IMEDIA_MAIN_HANDLERS_IBLOCK_ELEMENT_ERROR_DELETE_INFO'));
                return false;
            default:
                break;
        }
    }

    public static function onAfterIBlockElementDelete()
    {
        $handler = self::getInstance();

        static::setIblockCode($handler->arFields);
        switch($handler->arFields['IBLOCK_CODE']){
            case 'CATALOG':
                static::removeFromDeffered($handler->arFields);
                break;
            case 'OFFERS':
                static::updateProductSizes($handler->arFields);
                break;
            default:
                break;
        }
    }

    /**
     * @param array $arFields
     * @return void
     */
    private static function setIblockCode(array &$arFields)
    {
        if(
            $arFields['IBLOCK_CODE']
            || (!((int) $arFields['IBLOCK_ID'] > 0))
        ){
            return;
        }

        $arFields['IBLOCK_CODE'] = IblockHelper::getCode((int) $arFields['IBLOCK_ID']);
    }

    /**
     * @param array $arFields
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function checkNeedRemoveFromDeffered(array &$arFields): void
    {
        if($arFields['ACTIVE'] !== 'N'){
            return;
        }

        static::setIblockCode($arFields);

        if($arFields['IBLOCK_CODE'] !== 'CATALOG'){
            return;
        }

        static::removeFromDeffered($arFields);
    }

    /**
     * @param array $arFields
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function removeFromDeffered(array $arFields): void
    {
        $id = (int) $arFields['ID'];

        if(!($id > 0)){
            return;
        }

        $query = DefferedProductTable::getList(
            [
                'filter' => [
                    '=ELEMENT_ID' => $id
                ]
            ]
        );

        while($obj = $query->fetchObject()){
            $obj->delete();
        }
    }

    /**
     * @param array $arFields
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function onAddnUpdate(array &$arFields): void
    {
        if ($arFields['BREAK'] === 'Y') {
            return;
        }

        $arChangeProperties = [];

        static::setIblockCode($arFields);
        switch($arFields['IBLOCK_CODE']){
            case 'CATALOG':
                $arProperties = static::getProperties(
                    $arFields,
                    [
                        Property::getCode('BRAND'),
                        Property::getCode('BRAND_NAME'),
                        Property::getCode('SIZE')
                    ]
                );
                $arProductFields = static::getProductFields($arFields);
                static::updateProductSize($arChangeProperties, $arProperties, $arProductFields);
                Deactivate::process((int) $arFields['ID']);
                static::setBrand($arChangeProperties, $arProperties);
                break;
            case 'OFFERS':
                static::updateProductSizes($arFields);
                Deactivate::process((int) $arFields['ID']);
                break;
            default:
                break;
        }

        if(!empty($arChangeProperties)){

            \CIBlockElement::SetPropertyValuesEx(
                $arFields['ID'],
                $arFields['IBLOCK_ID'],
                $arChangeProperties
            );

            Manager::updateElementIndex($arFields['IBLOCK_ID'], $arFields['ID']);

            if($arFields['IBLOCK_CODE'] === 'CATALOG'){
                \CBitrixComponent::clearComponentCache('imedia:catalog.smart.filter');
            }

        }
    }

    /**
     * @param array $arFields
     * @param array $code
     * @return array
     */
    private static function getProperties(array $arFields, array $code): array
    {
        \CIBlockElement::GetPropertyValuesArray(
            $arProperties,
            $arFields['IBLOCK_ID'],
            ['ID' => $arFields['ID']],
            ['CODE' => $code],
            [
                'PROPERTY_FIELDS' => ['ID', 'VALUE'],
                'GET_RAW_DATA' => 'Y'
            ]
        );

        return (array) $arProperties[$arFields['ID']];
    }

    /**
     * @param array $arChangeProperties
     * @param array $arProperties
     * @return void
     */
    private static function setBrand(array &$arChangeProperties, array $arProperties): void
    {
        $brandName = $arProperties[Property::getCode('BRAND_NAME')]['VALUE'];
        $newPropertyValue = ($brandName) ? Brand::getBrandId($brandName) : null;
        $currentPropertyValue = (int) $arProperties[Property::getCode('BRAND')]['VALUE'];

        if($newPropertyValue !== $currentPropertyValue){
            $arChangeProperties[Property::getCode('BRAND')] = $newPropertyValue;
        }
    }

    /**
     * @param array $arFields
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function getProductFields(array $arFields): array
    {
        if(!((int) $arFields['ID'] > 0)){
            return [];
        }

        Loader::includeModule('catalog');

        return (array) ProductTable::getList(
            [
                'filter' => ['=ID' => $arFields['ID']],
                'limit' => 1
            ]
        )->fetch();
    }

    /**
     * @param array $arChangeProperties
     * @param array $arProperties
     * @param array $arProductFields
     * @return void
     */
    private static function updateProductSize(
        array &$arChangeProperties,
        array $arProperties,
        array $arProductFields
    ): void
    {
        if((int) $arProductFields['TYPE'] !== ProductTable::TYPE_PRODUCT){
            return;
        }

        $arChangeProperties['FILTER_SIZE'] = $arProperties[Property::getCode('SIZE')]['VALUE'];
    }

    /**
     * @param array $arFields
     * @return void
     */
    private static function updateProductSizes(array $arFields): void
    {
        if(!$arFields['PROPERTY_' . Property::getCode('CML2_LINK') . '_VALUE']){

            $arFilter = [
                '=ID' => $arFields['ID'],
                '=IBLOCK_ID' => $arFields['IBLOCK_ID']
            ];

            $arSelect = ['PROPERTY_' . Property::getCode('CML2_LINK')];

            $query = \CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
            if($row = $query->GetNext(true, false)){
                $arFields['PROPERTY_' . Property::getCode('CML2_LINK') . '_VALUE']
                    = $row['PROPERTY_' . Property::getCode('CML2_LINK') . '_VALUE'];
            }

        }

        $parentId = (int) $arFields['PROPERTY_' . Property::getCode('CML2_LINK') . '_VALUE'];

        if(!($parentId > 0)){
            return;
        }

        $arProperties = [];

        \CIBlockElement::GetPropertyValuesArray(
            $arProperties,
            $arFields['IBLOCK_ID'],
            ['PROPERTY_' . Property::getCode('CML2_LINK') => $parentId],
            ['CODE' => Property::getCode('SIZE')],
            [
                'PROPERTY_FIELDS' => ['ID', 'VALUE'],
                'GET_RAW_DATA' => 'Y'
            ]
        );

        $arValues = [];

        foreach($arProperties as $arItemProperties){

            if($arItemProperties[Property::getCode('SIZE')]['VALUE']){
                $arValues[] = $arItemProperties[Property::getCode('SIZE')]['VALUE'];
            }

        }

        $iblockId = IblockHelper::getId('CATALOG');

        \CIBlockElement::SetPropertyValuesEx(
            $parentId,
            $iblockId,
            ['FILTER_SIZE' => array_unique($arValues)]
        );

        Manager::updateElementIndex($iblockId, $parentId);
    }

    protected static function removeEmptyPropertiesFromImport(array &$arFields): void
    {
        $request = Context::getCurrent()->getRequest();
        if($request->get('mode') !== 'import'){
            return;
        }

        if(!$arFields['DETAIL_TEXT']){

            $arElement = \CIBlockElement::GetList(
                [],
                [
                    '=IBLOCK_ID' => $arFields['IBLOCK_ID'],
                    '=ID' => $arFields['ID']
                ],
                false,
                ['nTopCount' => 1],
                ['DETAIL_TEXT']
            )->GetNext(true, false);

            $arFields['DETAIL_TEXT'] = $arElement['DETAIL_TEXT'];
            $arFields['DETAIL_TEXT_TYPE'] = $arElement['DETAIL_TEXT_TYPE'];

        }

        \CIBlockElement::GetPropertyValuesArray(
            $arProperties,
            $arFields['IBLOCK_ID'],
            ['ID' => $arFields['ID']],
            [],
            [
                'PROPERTY_FIELDS' => ['ID', 'VALUE', 'PROPERTY_TYPE', 'MULTIPLE'],
                'GET_RAW_DATA' => 'Y'
            ]
        );

        $arPropertiesMap = [];

        foreach($arProperties[$arFields['ID']] as $arProperty){

            if(
                !$arProperty['VALUE']
                || ($arProperty['MULTIPLE'] === 'Y')
                || (!in_array($arProperty['PROPERTY_TYPE'], [
                    PropertyTable::TYPE_STRING,
                    PropertyTable::TYPE_NUMBER
                ], true))
            ){
                continue;
            }

            $arPropertiesMap[$arProperty['ID']] = $arProperty['VALUE'];

        }

        foreach($arFields['PROPERTY_VALUES'] as $propertyId => $arProperty){

            if(!isset($arPropertiesMap[$propertyId])){
                continue;
            }

            $firstKey = array_key_first($arProperty);

            if(
                array_key_exists('VALUE', $arProperty[$firstKey])
                && (
                    ($arProperty[$firstKey]['VALUE'] === null)
                    || ($arProperty[$firstKey]['VALUE'] === '')
                )
            ){
                $arFields['PROPERTY_VALUES'][$propertyId][$firstKey]['VALUE'] = $arPropertiesMap[$propertyId];
            }

        }

    }
}