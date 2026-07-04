<?php
namespace Imedia\Main\Helpers\Catalog\Service;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock\PropertyTable;
use Imedia\Main\Models\CatalogUpdate\CatalogUpdateTable;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class UpdateProperties
{
    protected int $timeBegin;
    protected int $total;
    protected int $limitTime;
    protected int $limitItems;

    protected const LIMIT_TIME = 30;
    protected const LIMIT_ITEMS = 100;
    protected const PREFIX_FIELD = 'FIELD_';
    protected const MODULE_ID = 'imedia.main';

    protected function __construct()
    {
        $this->timeBegin = microtime();
        $this->total = 0;
        $this->limitTime = (int) Option::get(
            static::MODULE_ID,
            'catalog_update_limit_time',
            static::LIMIT_TIME
        );
        $this->limitItems = (int) Option::get(
            static::MODULE_ID,
            'catalog_update_limit_items',
            static::LIMIT_ITEMS
        );
    }

    public static function process(): Result
    {
        $result = new Result();
        $process = new static();

        try {

            $arItems = $process->getItems();
            if(empty($arItems)){
                return $result;
            }

            Loader::includeModule('iblock');

            $arConfig = static::getConfig();
            if(empty($arConfig)){
                return $result;
            }

            Loader::includeModule('catalog');

            $arBridge = static::getBridge(array_keys($arItems));

            $process->processData($arItems, $arBridge, $arConfig);

        } catch (\Exception $e){

            $result->addError(new Error($e->getMessage(), $e->getCode()));

        }

        $result->setData(
            [
                'TOTAL' => $process->total
            ]
        );

        return $result;
    }

    protected function getItems(): array
    {
        $arItems = [];

        $ids = [];

        $query = CatalogUpdateTable::getList(
            [
                'limit' => $this->limitItems,
                'order' => ['ID' => 'ASC']
            ]
        );
        while($row = $query->fetch()){
            $arItems[$row['INTERNAL_CODE']][$row['DATA']['PROPERTY']] = $row['DATA']['VALUE'];
            $arItems[$row['INTERNAL_CODE']]['ID'][] = $row['ID'];
            $ids[] = $row['ID'];
        }

        if(!empty($ids)){

            $query = CatalogUpdateTable::getList(
                [
                    'filter' => [
                        '=INTERNAL_CODE' => array_keys($arItems),
                        '!ID' => $ids
                    ]
                ]
            );
            while($row = $query->fetch()){
                $arItems[$row['INTERNAL_CODE']][$row['DATA']['PROPERTY']] = $row['DATA']['VALUE'];
                $arItems[$row['INTERNAL_CODE']]['ID'][] = $row['ID'];
            }

        }

        return $arItems;
    }

    protected static function getBridge(array $internalCodes): array
    {
        $arBridge = [];

        $arFilter = [
            '=TYPE' => [
                ProductTable::TYPE_SKU,
                ProductTable::TYPE_PRODUCT
            ],
            '=PROPERTY_' . Property::getCode('INTERNAL_CODE') => $internalCodes
        ];

        $arSelect = ['ID', 'IBLOCK_ID', 'PROPERTY_' . Property::getCode('INTERNAL_CODE')];

        $query = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while($row = $query->GetNext(true, false)){

            $arBridge[ $row['PROPERTY_' . Property::getCode('INTERNAL_CODE') . '_VALUE' ] ] = [
                'ID' => (int) $row['ID'],
                'IBLOCK_ID' => (int) $row['IBLOCK_ID']
            ];

        }

        return $arBridge;
    }

    protected static function getConfig(): array
    {
        $config = [];

        $properties = Option::get(static::MODULE_ID, 'catalog_update_properties');
        if($properties){
            $properties = Json::decode($properties);
        }

        $codeList = [];

        foreach($properties as $property){
            $code = $property['VALUE'] ?: $property['ID'];
            $codeList[] = $property['ID'];
            $config[ $code ] = [
                'CODE' => $property['ID'],
                'TYPE' => ''
            ];
        }

        if(empty($codeList)){
            return $config;
        }

        $propertyType = [];
        $query = PropertyTable::getList(
            [
                'select' => ['CODE', 'PROPERTY_TYPE'],
                'filter' => [
                    '=IBLOCK_ID' => IblockHelper::getId('CATALOG'),
                    '=CODE' => $codeList
                ]
            ]
        );
        while($row = $query->fetch()){
            $propertyType[$row['CODE']] = $row['PROPERTY_TYPE'];
        }

        foreach($config as $key => $arValue){
            $config[$key]['TYPE'] = $propertyType[$arValue['CODE']];
        }

        return $config;
    }

    protected function getTimeHasPassed(): int
    {
        return microtime() - $this->timeBegin;
    }

    protected function processData(
        array $arItems,
        array $arBridge,
        array $arConfig
    ): void
    {
        foreach($arItems as $internalCode => $arItem){

            $arProduct = $arBridge[$internalCode];

            if($arProduct['ID'] > 0){

                $arFields = [];
                $arProperties = [];

                foreach($arItem as $key => $value){

                    $code = $arConfig[$key]['CODE'];
                    if(!$code){
                        continue;
                    }

                    if(strpos($code, static::PREFIX_FIELD) === 0){

                        $field = str_replace(static::PREFIX_FIELD, '', $code);

                        switch($field){
                            case 'DETAIL_PICTURE':
                                $value = \CFile::MakeFileArray($value);
                                break;
                            case 'DETAIL_TEXT':
                                $value = static::clearText($value);
                                $arFields['DETAIL_TEXT_TYPE'] = 'html';
                                break;
                            default:
                                continue 2;
                        }

                        $arFields[$field] = $value;

                    } else {

                        switch($arConfig[$key]['TYPE']){
                            case PropertyTable::TYPE_STRING:
                            case PropertyTable::TYPE_NUMBER:
                                break;
                            case PropertyTable::TYPE_FILE:

                                if(is_array($value)){

                                    $temp = [];
                                    foreach($value as $item){
                                        $temp[] = \CFile::MakeFileArray($item);
                                    }

                                    $value = $temp;

                                } else {
                                    $value = \CFile::MakeFileArray($value);
                                }

                                break;
                            default:
                                continue 2;
                        }

                        $arProperties[$code] = $value;
                    }

                }

                if(!empty($arFields)){

                    $el = new \CIBlockElement();
                    $el->Update($arProduct['ID'], $arFields);

                }

                if(!empty($arProperties)){

                    \CIBlockElement::SetPropertyValuesEx(
                        $arProduct['ID'],
                        $arProduct['IBLOCK_ID'],
                        $arProperties
                    );

                }

            }

            foreach($arItem['ID'] as $id){

                CatalogUpdateTable::delete($id);
                $this->total++;

            }

            if($this->getTimeHasPassed() >= $this->limitTime){
                break;
            }

        }
    }

    protected static function clearText(string $value): string
    {
        $toRemove = ['\n', '\r'];

        $value = str_replace($toRemove, '', $value);

        return $value;
    }
}